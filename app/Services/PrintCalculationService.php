<?php

namespace App\Services;

use App\Models\RawMaterial;
use App\Models\StandardSheet;
use InvalidArgumentException;

class PrintCalculationService
{
    /**
     * Calculate printing paper requirements for a job.
     *
     * @param array<string, mixed> $input
     * @return array<string, int|float|string>
     */
    public function calculate(array $input): array
    {
        $totalPages = (int) ($input['total_pages'] ?? 1);
        $totalCopies = (int) ($input['total_copies'] ?? 0);
        $colors = (int) ($input['colors'] ?? 4);

        if ($totalCopies < 1) {
            throw new InvalidArgumentException('total_copies must be greater than zero.');
        }

        $pageSize = (string) ($input['page_size'] ?? 'A4');
        [$pageWidth, $pageHeight] = $this->resolvePageSize(
            $pageSize,
            isset($input['custom_width']) ? (float) $input['custom_width'] : null,
            isset($input['custom_height']) ? (float) $input['custom_height'] : null,
        );

        [$sheetWidth, $sheetHeight, $rawMaterialCost] = $this->resolveRawSheet(
            isset($input['raw_material_id']) ? (int) $input['raw_material_id'] : null,
            strtolower((string) ($input['standard_sheet_size'] ?? 'demy'))
        );

        $fitOne = intdiv((int) floor($sheetWidth * 100), (int) max(1, floor($pageWidth * 100)));
        $fitTwo = intdiv((int) floor($sheetHeight * 100), (int) max(1, floor($pageHeight * 100)));
        $pagesPerSide = max(1, $fitOne * $fitTwo);

        $pagesPerSheet = $pagesPerSide;

        $pagesPerFullSheet = max(1, $pagesPerSheet);
        $rawSheets = (int) ceil(($totalPages * $totalCopies) / $pagesPerFullSheet);

        $wastagePerColor = (float) ($input['wastage_per_color'] ?? config('printing.wastage.default_per_color_percent', 2));
        $minimumWastage = (float) config('printing.wastage.minimum_percent', 5);
        $maximumWastage = (float) config('printing.wastage.maximum_percent', 8);
        $wastagePercentage = min(max($colors * $wastagePerColor, $minimumWastage), $maximumWastage);

        $wastageSheets = (int) ceil($rawSheets * $wastagePercentage / 100);
        $totalSheets = $rawSheets + $wastageSheets;
        $totalRawMaterialCost = $totalSheets * $rawMaterialCost;

        $roundedSheets = (int) (ceil($totalSheets / 25) * 25);
        $reams = intdiv($roundedSheets, 500);
        $remainingAfterReam = $roundedSheets % 500;
        $quires = intdiv($remainingAfterReam, 25);
        $remainderSheets = $totalSheets % 25;

        return [
            'pages_per_sheet' => $pagesPerSheet,
            'raw_sheets' => $rawSheets,
            'wastage_percentage' => $wastagePercentage,
            'wastage_sheets' => $wastageSheets,
            'total_sheets' => $totalSheets,
            'reams' => $reams,
            'quires' => $quires,
            'remainder_sheets' => $remainderSheets,
            'summary' => sprintf('%d Reams, %d Quires, %d sheets', $reams, $quires, $remainderSheets),
            'sheet_width' => $sheetWidth,
            'sheet_height' => $sheetHeight,
            'total_pages_used' => $totalPages,
            'raw_material_unit_cost' => round($rawMaterialCost, 2),
            'total_raw_material_cost' => round($totalRawMaterialCost, 2),
        ];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function resolvePageSize(string $pageSize, ?float $customWidth, ?float $customHeight): array
    {
        $sizes = [
            'a4' => [8.27, 11.69],
            'a5' => [5.83, 8.27],
            'letter' => [8.5, 11],
            '8.5x11' => [8.5, 11],
        ];

        if (strtolower($pageSize) === 'custom') {
            if (! $customWidth || ! $customHeight) {
                throw new InvalidArgumentException('custom_width and custom_height are required for custom page size.');
            }

            return [$customWidth, $customHeight];
        }

        $resolved = $sizes[strtolower($pageSize)] ?? null;

        if (! $resolved) {
            throw new InvalidArgumentException('Unsupported page_size value.');
        }

        return $resolved;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function resolveSheetSize(string $sheetSize): array
    {
        $resolved = match ($sheetSize) {
            'demy' => [17.5, 22.5],
            'crown' => [15, 20],
            'double_crown' => [20, 30],
            'royal' => [20, 25],
            default => null,
        };

        if ($resolved) {
            return $resolved;
        }

        $custom = StandardSheet::query()
            ->where('code', $sheetSize)
            ->first();

        if ($custom) {
            return [(float) $custom->width_in, (float) $custom->height_in];
        }

        throw new InvalidArgumentException('Unsupported standard_sheet_size value.');
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function resolveRawSheet(?int $rawMaterialId, string $fallbackSheetSize): array
    {
        if ($rawMaterialId) {
            $raw = RawMaterial::query()->find($rawMaterialId);
            if (! $raw) {
                throw new InvalidArgumentException('Invalid raw material selected.');
            }

            $width = (float) ($raw->width_in ?? 0);
            $height = (float) ($raw->height_in ?? 0);
            if ($width <= 0 || $height <= 0) {
                throw new InvalidArgumentException('Raw material width and height are required.');
            }

            return [$width, $height, (float) $raw->average_cost];
        }

        [$width, $height] = $this->resolveSheetSize($fallbackSheetSize);

        return [$width, $height, 0.0];
    }
}
