<?php

namespace App\Services;

class DieGeometryService
{
    public function generateCartonPolygon(array $d): array
    {
        $bodyW = max((float) ($d['body_width_mm'] ?? 0), 1.0);
        $bodyH = max((float) ($d['body_height_mm'] ?? 0), 1.0);
        $top = max((float) ($d['top_flap_mm'] ?? 0), 0.0);
        $bottom = max((float) ($d['bottom_flap_mm'] ?? 0), 0.0);
        $side = max((float) ($d['side_flap_mm'] ?? 0), 0.0);
        $glue = max((float) ($d['glue_flap_mm'] ?? 0), 0.0);
        $bleed = max((float) ($d['bleed_mm'] ?? 3), 0.0);

        // Carton-style die following the provided sample style:
        // total flat width = (2 * bodyW) + (2 * side) + glue
        // Includes top/bottom side flaps on both sides with computed angled cuts
        // (matching the hand-marked sample tabs).
        $x0 = 0.0;
        $x1 = $glue;
        $x2 = $x1 + $side;
        $x3 = $x2 + $bodyW;
        $x4 = $x3 + $side;
        $x5 = $x4 + $bodyW;
        $y0 = 0.0;
        $y1 = $top;
        $y2 = $top + $bodyH;
        $y3 = $y2 + $bottom;
        $miniInsetTop = min($top * 0.18, $top);
        $miniInsetBottom = min($bottom * 0.18, $bottom);
        $poly = [
            ['x' => $x0, 'y' => $y1],
            ['x' => $x1, 'y' => $y1],
            // Top mini-left + top flap + top mini-right (one-side flap block)
            ['x' => $x1, 'y' => $y0 +$miniInsetTop],
            ['x' => $x2, 'y' => $y0],
            ['x' => $x3, 'y' => $y0],
            ['x' => $x4, 'y' => $y0 + $miniInsetTop],
            ['x' => $x4, 'y' => $y1],
            ['x' => $x5, 'y' => $y1],
            ['x' => $x5, 'y' => $y2],
            ['x' => $x4, 'y' => $y2],       
            // Bottom mini-right + bottom flap + bottom mini-left (one-side flap block)
            ['x' => $x4, 'y' => $y3 - $miniInsetBottom],
            ['x' => $x3, 'y' => $y3],
            ['x' => $x2, 'y' => $y3],
            ['x' => $x1, 'y' => $y3 - $miniInsetBottom],
            ['x' => $x1, 'y' => $y2],
            ['x' => $x0, 'y' => $y2],
            
        ];

        if ($bleed > 0) {
            // Approximate bleed while preserving non-rectangular profile.
            $poly = array_map(function (array $p) use ($x0, $x2, $x3, $x5, $y0, $y1, $y2, $y3, $bleed): array {
                $nx = $p['x'];
                $ny = $p['y'];
                if (abs($p['x'] - $x0) < 0.0001) $nx -= $bleed;
                if (abs($p['x'] - $x5) < 0.0001) $nx += $bleed;
                if (abs($p['y'] - $y0) < 0.0001) $ny -= $bleed;
                if (abs($p['y'] - $y3) < 0.0001) $ny += $bleed;
                if (abs($p['x'] - $x2) < 0.0001 && ($p['y'] <= $y1 || $p['y'] >= $y2)) $nx -= $bleed;
                if (abs($p['x'] - $x3) < 0.0001 && ($p['y'] <= $y1 || $p['y'] >= $y2)) $nx += $bleed;
                return ['x' => $nx, 'y' => $ny];
            }, $poly);
        }

        return $this->normalizePolygon($this->sanitizePolygon($poly));
    }

    public function normalizePolygon(array $points): array
    {
        $bbox = $this->boundingBox($points);

        return array_map(fn ($p) => ['x' => round($p['x'] - $bbox['min_x'], 3), 'y' => round($p['y'] - $bbox['min_y'], 3)], $points);
    }

    public function boundingBox(array $points): array
    {
        $xs = array_map(fn ($p) => (float) $p['x'], $points);
        $ys = array_map(fn ($p) => (float) $p['y'], $points);

        return [
            'min_x' => min($xs),
            'min_y' => min($ys),
            'max_x' => max($xs),
            'max_y' => max($ys),
            'width' => max($xs) - min($xs),
            'height' => max($ys) - min($ys),
        ];
    }

    public function polygonArea(array $points): float
    {
        $sum = 0.0;
        $n = count($points);
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $sum += ($points[$i]['x'] * $points[$j]['y']) - ($points[$j]['x'] * $points[$i]['y']);
        }

        return abs($sum) / 2.0;
    }

    public function rotate(array $points, int $deg): array
    {
        $rad = deg2rad($deg % 360);
        $cos = cos($rad);
        $sin = sin($rad);
        $rot = array_map(function ($p) use ($cos, $sin) {
            $x = (float) $p['x'];
            $y = (float) $p['y'];

            return ['x' => $x * $cos - $y * $sin, 'y' => $x * $sin + $y * $cos];
        }, $points);

        return $this->normalizePolygon($rot);
    }

    public function mirrorX(array $points): array
    {
        $bbox = $this->boundingBox($points);
        $maxX = $bbox['max_x'];
        $mirrored = array_map(fn ($p) => ['x' => $maxX - (float) $p['x'], 'y' => (float) $p['y']], $points);

        return $this->normalizePolygon($mirrored);
    }

    public function translate(array $points, float $dx, float $dy): array
    {
        return array_map(fn ($p) => ['x' => $p['x'] + $dx, 'y' => $p['y'] + $dy], $points);
    }

    public function intersects(array $a, array $b): bool
    {
        if (! $this->bboxIntersects($this->boundingBox($a), $this->boundingBox($b))) {
            return false;
        }

        return $this->satIntersects($a, $b);
    }

    public function polygonToSvgPath(array $points): string
    {
        $chunks = [];
        foreach ($points as $idx => $p) {
            $chunks[] = ($idx === 0 ? 'M' : 'L') . round($p['x'], 3) . ' ' . round($p['y'], 3);
        }

        return implode(' ', $chunks) . ' Z';
    }

    private function bboxIntersects(array $a, array $b): bool
    {
        return ! ($a['max_x'] <= $b['min_x'] || $a['min_x'] >= $b['max_x'] || $a['max_y'] <= $b['min_y'] || $a['min_y'] >= $b['max_y']);
    }

    private function satIntersects(array $polyA, array $polyB): bool
    {
        foreach ([$polyA, $polyB] as $poly) {
            $count = count($poly);
            for ($i = 0; $i < $count; $i++) {
                $j = ($i + 1) % $count;
                $edgeX = $poly[$j]['x'] - $poly[$i]['x'];
                $edgeY = $poly[$j]['y'] - $poly[$i]['y'];
                $axisX = -$edgeY;
                $axisY = $edgeX;

                [$aMin, $aMax] = $this->project($polyA, $axisX, $axisY);
                [$bMin, $bMax] = $this->project($polyB, $axisX, $axisY);

                if ($aMax <= $bMin || $bMax <= $aMin) {
                    return false;
                }
            }
        }

        return true;
    }

    private function project(array $poly, float $axisX, float $axisY): array
    {
        $dots = array_map(fn ($p) => $p['x'] * $axisX + $p['y'] * $axisY, $poly);

        return [min($dots), max($dots)];
    }

    private function sanitizePolygon(array $points): array
    {
        $clean = [];
        foreach ($points as $p) {
            $x = round((float) $p['x'], 6);
            $y = round((float) $p['y'], 6);
            $last = end($clean);
            if ($last !== false && abs($last['x'] - $x) < 0.000001 && abs($last['y'] - $y) < 0.000001) {
                continue;
            }
            $clean[] = ['x' => $x, 'y' => $y];
        }
        if (count($clean) > 1) {
            $first = $clean[0];
            $last = end($clean);
            if (abs($first['x'] - $last['x']) < 0.000001 && abs($first['y'] - $last['y']) < 0.000001) {
                array_pop($clean);
            }
        }

        return $clean;
    }
}
