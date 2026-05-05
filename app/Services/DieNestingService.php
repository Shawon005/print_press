<?php

namespace App\Services;

class DieNestingService
{
    public function __construct(private readonly DieGeometryService $geometry)
    {
    }

    public function nest(array $shapePoints, float $sheetW, float $sheetH, float $gap, bool $allowMirror = false): array
    {
        $modes = [
            'normal' => ['interlock' => false],
            'rotated' => ['interlock' => false],
            'interlocked' => ['interlock' => true],
        ];

        $best = null;

        foreach ($modes as $mode => $opts) {
            $result = $this->runPattern($shapePoints, $sheetW, $sheetH, $gap, $opts['interlock'], $mode === 'rotated', $allowMirror);
            if ($best === null || $result['box_count'] > $best['box_count']) {
                $best = $result;
            }
        }

        return $best ?? ['layout_mode' => 'normal', 'placements' => [], 'box_count' => 0, 'used_area_mm2' => 0];
    }

    public function nestCartonInterlockedByDimensions(array $shapePoints, array $dimsMm, float $sheetW, float $sheetH, float $gap): ?array
    {
        $top = max((float) ($dimsMm['top_flap_mm'] ?? 0), 0.0);
        $bottom = max((float) ($dimsMm['bottom_flap_mm'] ?? 0), 0.0);
        if ($top <= 0 && $bottom <= 0) {
            return null;
        }

        $bbox = $this->geometry->boundingBox($shapePoints);
        $shapeW = max((float) $bbox['width'], 1.0);
        $shapeH = max((float) $bbox['height'], 1.0);
        $baseArea = $this->geometry->polygonArea($shapePoints);
        $bestPlacements = [];
        $bestOffset = 0.0;
        $bestYStep = $shapeH + $gap;

        $xStepCandidates = [
            max($shapeW * 0.62, $gap + 1.0),
            max($shapeW * 0.68, $gap + 1.0),
            max($shapeW * 0.74, $gap + 1.0),
            max($shapeW * 0.8, $gap + 1.0),
            $shapeW + $gap,
        ];
        $staggerCandidates = [
            max($shapeW * 0.08, 0.0),
            max($shapeW * 0.12, 0.0),
            max($shapeW * 0.16, 0.0),
            max($shapeW * 0.2, 0.0),
            max($shapeW * 0.24, 0.0),
        ];
        $yStepCandidates = [
            max(($shapeH - ($top * 0.85)) + $gap, 1.0),
            max(($shapeH - ($top * 0.7)) + $gap, 1.0),
            max(($shapeH - min($top, $bottom)) + $gap, 1.0),
            max(($shapeH - (($top + $bottom) * 0.45)) + $gap, 1.0),
            max(($shapeH - (($top + $bottom) * 0.35)) + $gap, 1.0),
            $shapeH + $gap,
        ];

        foreach ($xStepCandidates as $xStep) {
            foreach ($staggerCandidates as $staggerX) {
                foreach ($yStepCandidates as $yStep) {
                    $placements = [];
                    $row = 0;
                    $y = 0.0;

                    while (($y + $shapeH) <= $sheetH + 0.0001) {
                        $x = ($row % 2 === 1) ? $staggerX : 0.0;
                        $rowPlaced = false;

                        while (($x + $shapeW) <= $sheetW + 0.0001) {
                            $translated = $this->geometry->translate($shapePoints, $x, $y);
                            $collides = false;
                            foreach ($placements as $placed) {
                                if ($this->geometry->intersects($translated, $placed['points'])) {
                                    $collides = true;
                                    break;
                                }
                            }

                            if (! $collides) {
                                $placements[] = [
                                    'x_mm' => round($x, 3),
                                    'y_mm' => round($y, 3),
                                    'rotation' => 0,
                                    'points' => $translated,
                                ];
                                $rowPlaced = true;
                            }

                            $x += $xStep;
                        }

                        if (! $rowPlaced && $row > 0) {
                            break;
                        }

                        $row++;
                        $y += $yStep;
                    }

                    if (count($placements) > count($bestPlacements)) {
                        $bestPlacements = $placements;
                        $bestOffset = $staggerX;
                        $bestYStep = $yStep;
                    }
                }
            }
        }

        if (count($bestPlacements) < 1) {
            return null;
        }

        return [
            'layout_mode' => 'interlocked',
            'layout_mode_detail' => 'interlocked-staggered-carton-safe',
            'stagger_offset_mm' => round($bestOffset, 3),
            'row_step_mm' => round($bestYStep, 3),
            'base_rotation' => 0,
            'placements' => $bestPlacements,
            'box_count' => count($bestPlacements),
            'used_area_mm2' => $baseArea * count($bestPlacements),
        ];
    }

    private function runPattern(array $shapePoints, float $sheetW, float $sheetH, float $gap, bool $interlock, bool $favorRotate, bool $allowMirror): array
    {
        $rotations = $favorRotate ? [90, 270, 0, 180] : [0, 90, 180, 270];
        $baseArea = $this->geometry->polygonArea($shapePoints);
        $bestPlacements = [];
        $bestOffset = 0.0;
        $bestRotation = $rotations[0] ?? 0;

        foreach ($rotations as $deg) {
            $seedShape = $this->geometry->rotate($shapePoints, $deg);
            $seedBbox = $this->geometry->boundingBox($seedShape);
            $offsets = $interlock
                ? [0.0, ($seedBbox['width'] / 2.0), max(($seedBbox['width'] / 2.0) - ($gap / 2.0), 0.0)]
                : [0.0];

            foreach ($offsets as $staggerOffset) {
                $placements = $this->packRows($shapePoints, $sheetW, $sheetH, $gap, $deg, $interlock, $allowMirror, $staggerOffset);
                if (count($placements) > count($bestPlacements)) {
                    $bestPlacements = $placements;
                    $bestOffset = $staggerOffset;
                    $bestRotation = $deg;
                }
            }
        }

        $baseMode = $interlock ? 'interlocked' : ($favorRotate ? 'rotated' : 'normal');
        $modeDetail = $baseMode;
        if ($interlock) {
            $modeDetail = 'interlocked-staggered';
            if ($bestOffset > 0) {
                $modeDetail .= '-offset-' . round($bestOffset, 2) . 'mm';
            }
        }

        return [
            'layout_mode' => $baseMode,
            'layout_mode_detail' => $modeDetail,
            'stagger_offset_mm' => round($bestOffset, 3),
            'base_rotation' => $bestRotation,
            'placements' => $bestPlacements,
            'box_count' => count($bestPlacements),
            'used_area_mm2' => $baseArea * count($bestPlacements),
        ];
    }

    private function packRows(array $shapePoints, float $sheetW, float $sheetH, float $gap, int $baseRotation, bool $interlock, bool $allowMirror, float $staggerOffset): array
    {
        $placements = [];
        $cursorY = 0.0;
        $rowIndex = 0;
        $prevRowHeight = 0.0;

        while ($cursorY < $sheetH) {
            $rowPlaced = false;
            $rowShape = $this->geometry->rotate($shapePoints, $baseRotation);

            if ($allowMirror && ($rowIndex % 2 === 1)) {
                $rowShape = $this->geometry->mirrorX($rowShape);
            }

            $rowBbox = $this->geometry->boundingBox($rowShape);
            $rowStepX = $this->computeCompactStepX($rowShape, $gap);
            $cursorX = ($interlock && ($rowIndex % 2 === 1)) ? $staggerOffset : 0.0;

            while ($cursorX < $sheetW) {
                $translated = $this->geometry->translate($rowShape, $cursorX, $cursorY);
                $tb = $this->geometry->boundingBox($translated);

                if ($tb['max_x'] <= $sheetW && $tb['max_y'] <= $sheetH) {
                    $collides = false;
                    foreach ($placements as $placed) {
                        if ($this->geometry->intersects($translated, $placed['points'])) {
                            $collides = true;
                            break;
                        }
                    }

                    if (! $collides) {
                        $placements[] = [
                            'x_mm' => round($cursorX, 3),
                            'y_mm' => round($cursorY, 3),
                            'rotation' => $baseRotation,
                            'points' => $translated,
                        ];
                        $rowPlaced = true;
                    }
                }

                $cursorX += $rowStepX;
            }

            if (! $rowPlaced) {
                break;
            }

            $nextAdvance = max(2.0, $rowBbox['height'] + $gap);
            if ($interlock && $rowIndex >= 0) {
                $nextAdvance = $this->findInterlockedRowAdvance(
                    $shapePoints,
                    $placements,
                    $sheetW,
                    $sheetH,
                    $gap,
                    $baseRotation,
                    $allowMirror,
                    $staggerOffset,
                    $cursorY,
                    $rowIndex + 1,
                    $rowBbox['height']
                );
            }

            $prevRowHeight = $rowBbox['height'];
            $cursorY += $nextAdvance;
            $rowIndex++;
        }

        return $placements;
    }

    private function findInterlockedRowAdvance(
        array $shapePoints,
        array $placements,
        float $sheetW,
        float $sheetH,
        float $gap,
        int $baseRotation,
        bool $allowMirror,
        float $staggerOffset,
        float $currentY,
        int $nextRowIndex,
        float $rowHeight
    ): float {
        $ratios = [0.42, 0.46, 0.5, 0.54, 0.58, 0.62, 0.7, 0.82, 1.0];

        foreach ($ratios as $ratio) {
            $advance = max(2.0, ($rowHeight * $ratio) + $gap);
            $testY = $currentY + $advance;
            if ($testY >= $sheetH) {
                continue;
            }

            $rowShape = $this->geometry->rotate($shapePoints, $baseRotation);
            if ($allowMirror && ($nextRowIndex % 2 === 1)) {
                $rowShape = $this->geometry->mirrorX($rowShape);
            }

            $rowBbox = $this->geometry->boundingBox($rowShape);
            $stepX = $this->computeCompactStepX($rowShape, $gap);
            $x = ($nextRowIndex % 2 === 1) ? $staggerOffset : 0.0;

            while ($x < $sheetW) {
                $candidate = $this->geometry->translate($rowShape, $x, $testY);
                $tb = $this->geometry->boundingBox($candidate);
                if ($tb['max_x'] <= $sheetW && $tb['max_y'] <= $sheetH) {
                    $collides = false;
                    foreach ($placements as $placed) {
                        if ($this->geometry->intersects($candidate, $placed['points'])) {
                            $collides = true;
                            break;
                        }
                    }

                    if (! $collides) {
                        return $advance;
                    }
                }

                $x += $stepX;
            }
        }

        return max(2.0, $rowHeight + $gap);
    }

    private function computeCompactStepX(array $shape, float $gap): float
    {
        $bbox = $this->geometry->boundingBox($shape);
        $defaultStep = max(2.0, $bbox['width'] + $gap);
        $minStep = max(1.0, $gap);
        $maxStep = $defaultStep;

        for ($dx = $minStep; $dx <= $maxStep; $dx += 1.0) {
            $shifted = $this->geometry->translate($shape, $dx, 0.0);
            if (! $this->geometry->intersects($shape, $shifted)) {
                return $dx;
            }
        }

        return $defaultStep;
    }
}
