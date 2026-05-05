<?php

namespace App\Http\Controllers;

use App\Models\DieLayout;
use App\Models\DieShape;
use App\Models\Tenant;
use App\Services\DieGeometryService;
use App\Services\DieNestingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DieNestingController extends Controller
{
    public function __construct(
        private readonly DieGeometryService $geometry,
        private readonly DieNestingService $nesting
    ) {
    }

    public function generateShape(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'body_width_mm' => ['nullable', 'numeric', 'min:0.01'],
            'body_height_mm' => ['nullable', 'numeric', 'min:0.01'],
            'top_flap_mm' => ['nullable', 'numeric', 'min:0'],
            'bottom_flap_mm' => ['nullable', 'numeric', 'min:0'],
            'side_flap_mm' => ['nullable', 'numeric', 'min:0'],
            'glue_flap_mm' => ['nullable', 'numeric', 'min:0'],
            'bleed_mm' => ['nullable', 'numeric', 'min:0'],
            'body_width_in' => ['nullable', 'numeric', 'min:0.01'],
            'body_height_in' => ['nullable', 'numeric', 'min:0.01'],
            'top_flap_in' => ['nullable', 'numeric', 'min:0'],
            'bottom_flap_in' => ['nullable', 'numeric', 'min:0'],
            'side_flap_in' => ['nullable', 'numeric', 'min:0'],
            'glue_flap_in' => ['nullable', 'numeric', 'min:0'],
            'bleed_in' => ['nullable', 'numeric', 'min:0'],
        ]);

        $mm = [
            'body_width_mm' => $this->resolveMm($data, 'body_width_mm', 'body_width_in'),
            'body_height_mm' => $this->resolveMm($data, 'body_height_mm', 'body_height_in'),
            'top_flap_mm' => $this->resolveMm($data, 'top_flap_mm', 'top_flap_in', 0.0),
            'bottom_flap_mm' => $this->resolveMm($data, 'bottom_flap_mm', 'bottom_flap_in', 0.0),
            'side_flap_mm' => $this->resolveMm($data, 'side_flap_mm', 'side_flap_in', 0.0),
            'glue_flap_mm' => $this->resolveMm($data, 'glue_flap_mm', 'glue_flap_in', 0.0),
            'bleed_mm' => $this->resolveMm($data, 'bleed_mm', 'bleed_in', 0.0),
        ];

        $points = $this->geometry->generateCartonPolygon($mm);
        $shape = DieShape::create([
            'tenant_id' => Tenant::first()?->id,
            'name' => $data['name'] ?? 'Generated Die Shape',
            'source' => 'generated',
            'dimensions_mm' => $mm,
            'polygon_points_mm' => $points,
            'svg_path' => $this->geometry->polygonToSvgPath($points),
        ]);

        return response()->json(['shape' => $shape]);
    }

    public function uploadSvg(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'die_svg' => ['required', 'file', 'mimes:svg', 'max:2048'],
        ]);

        $raw = file_get_contents($data['die_svg']->getRealPath()) ?: '';
        preg_match('/viewBox\s*=\s*"([^"]+)"/i', $raw, $m);
        $points = [
            ['x' => 0.0, 'y' => 0.0],
            ['x' => 100.0, 'y' => 0.0],
            ['x' => 100.0, 'y' => 100.0],
            ['x' => 0.0, 'y' => 100.0],
        ];

        if (! empty($m[1])) {
            $parts = preg_split('/\s+/', trim($m[1]));
            if (count($parts) === 4) {
                $w = max((float) $parts[2], 1.0);
                $h = max((float) $parts[3], 1.0);
                $points = [
                    ['x' => 0.0, 'y' => 0.0],
                    ['x' => $w, 'y' => 0.0],
                    ['x' => $w, 'y' => $h],
                    ['x' => 0.0, 'y' => $h],
                ];
            }
        }

        $shape = DieShape::create([
            'tenant_id' => Tenant::first()?->id,
            'name' => $data['name'] ?? 'Uploaded Die Shape',
            'source' => 'svg_upload',
            'polygon_points_mm' => $points,
            'svg_raw' => $raw,
            'svg_path' => $this->geometry->polygonToSvgPath($points),
        ]);

        return response()->json(['shape' => $shape]);
    }

    public function calculateLayout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'die_shape_id' => ['required', 'integer', 'exists:die_shapes,id'],
            'sheet_width_mm' => ['nullable', 'numeric', 'min:1'],
            'sheet_height_mm' => ['nullable', 'numeric', 'min:1'],
            'gap_mm' => ['nullable', 'numeric', 'min:0'],
            'sheet_width_in' => ['nullable', 'numeric', 'min:1'],
            'sheet_height_in' => ['nullable', 'numeric', 'min:1'],
            'gap_in' => ['nullable', 'numeric', 'min:0'],
            'allow_mirror' => ['nullable', 'boolean'],
        ]);

        $shape = DieShape::findOrFail($data['die_shape_id']);
        $sheetW = $this->resolveMm($data, 'sheet_width_mm', 'sheet_width_in');
        $sheetH = $this->resolveMm($data, 'sheet_height_mm', 'sheet_height_in');
        $gap = max($this->resolveMm($data, 'gap_mm', 'gap_in', 2.54), 0.0);
        $nest = $this->nesting->nest($shape->polygon_points_mm, $sheetW, $sheetH, $gap, (bool) ($data['allow_mirror'] ?? false));
        if ($shape->source === 'generated' && is_array($shape->dimensions_mm)) {
            $cartonNest = $this->nesting->nestCartonInterlockedByDimensions(
                $shape->polygon_points_mm,
                $shape->dimensions_mm,
                $sheetW,
                $sheetH,
                $gap
            );
            if ($cartonNest && ($cartonNest['box_count'] ?? 0) > ($nest['box_count'] ?? 0)) {
                $nest = $cartonNest;
            }
        }

        $sheetArea = $sheetW * $sheetH;
        $used = (float) $nest['used_area_mm2'];
        $wastage = max($sheetArea - $used, 0.0);
        $wastagePct = $sheetArea > 0 ? ($wastage / $sheetArea) * 100 : 0;

        $svg = $this->buildLayoutSvg($sheetW, $sheetH, $nest['placements']);

        $layout = DieLayout::create([
            'tenant_id' => Tenant::first()?->id,
            'die_shape_id' => $shape->id,
            'layout_mode' => (string) ($nest['layout_mode_detail'] ?? $nest['layout_mode']),
            'sheet_width_mm' => $sheetW,
            'sheet_height_mm' => $sheetH,
            'box_count' => $nest['box_count'],
            'used_area_mm2' => round($used, 3),
            'wastage_area_mm2' => round($wastage, 3),
            'wastage_percent' => round($wastagePct, 3),
            'placements_json' => $nest['placements'],
            'layout_svg' => $svg,
        ]);

        return response()->json([
            'layout' => $layout,
            'preview_svg' => $svg,
            'export_svg_url' => route('printing.die-layout.export-svg', $layout->id),
            'export_pdf_url' => route('printing.die-layout.export-pdf', $layout->id),
        ]);
    }

    public function exportSvg(DieLayout $layout): Response
    {
        return response($layout->layout_svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="die-layout-' . $layout->id . '.svg"');
    }

    public function exportPdf(DieLayout $layout): Response
    {
        $svg = (string) ($layout->layout_svg ?? '');
        $svgDataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        $html = '
            <html>
                <head>
                    <style>
                        @page { size: A4 landscape; margin: 10mm; }
                        html, body { margin: 0; padding: 0; background: #ffffff; font-family: DejaVu Sans, sans-serif; color: #111827; }
                        .sheet {
                            page-break-after: avoid;
                            page-break-inside: avoid;
                            overflow: hidden;
                        }
                        .header {
                            height: 16mm;
                            border: 1px solid #d1d5db;
                            padding: 2.2mm 4mm;
                            box-sizing: border-box;
                            margin-bottom: 2mm;
                        }
                        .title {
                            margin: 0;
                            font-size: 14pt;
                            font-weight: 700;
                            color: #0f172a;
                        }
                        .meta {
                            margin-top: 1mm;
                            font-size: 8.5pt;
                            color: #334155;
                        }
                        .layout-frame {
                            border: 1px solid #d1d5db;
                            box-sizing: border-box;
                            overflow: hidden;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .layout-frame img {
                            width: 1000px;
                            height: 800px;  
                        }
                    </style>
                </head>
                <body>
                    <div class="sheet">
                        <div class="header">
                            <p class="title">Die-Cut Layout Plan</p>
                            <div class="meta">
                                Layout ID: #' . (int) $layout->id . ' |
                                Mode: ' . e((string) $layout->layout_mode) . ' |
                                Boxes/Sheet: ' . (int) $layout->box_count . ' |
                                Wastage: ' . number_format((float) $layout->wastage_percent, 2) . '%
                            </div>
                        </div>
                        <div class="layout-frame">
                            <img src="' . $svgDataUri . '" alt="Die layout SVG preview" />
                        </div>
                    </div>
                </body>
            </html>
        ';

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

        return $pdf->download('die-layout-' . $layout->id . '.pdf');
    }

    private function buildLayoutSvg(float $sheetW, float $sheetH, array $placements): string
    {
        $paths = [];
        foreach ($placements as $placed) {
            $points = $placed['points'] ?? [];
            if (empty($points)) {
                continue;
            }
            $d = $this->geometry->polygonToSvgPath($points);
            $paths[] = '<path d="' . e($d) . '" fill="none" stroke="#1d4ed8" stroke-width="0.8" />';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . round($sheetW, 3) . ' ' . round($sheetH, 3) . '" width="' . round($sheetW, 3) . 'mm" height="' . round($sheetH, 3) . 'mm"><rect x="0" y="0" width="' . round($sheetW, 3) . '" height="' . round($sheetH, 3) . '" fill="#fff" stroke="#111827" stroke-width="0.8" />' . implode('', $paths) . '</svg>';
    }

    private function resolveMm(array $data, string $mmKey, string $inKey, ?float $default = null): float
    {
        if (isset($data[$mmKey]) && $data[$mmKey] !== null && $data[$mmKey] !== '') {
            return (float) $data[$mmKey];
        }
        if (isset($data[$inKey]) && $data[$inKey] !== null && $data[$inKey] !== '') {
            return (float) $data[$inKey] * 25.4;
        }
        return (float) ($default ?? 0.0);
    }
}
