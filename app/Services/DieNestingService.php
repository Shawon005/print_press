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

    private function runPattern(array $shapePoints, float $sheetW, float $sheetH, float $gap, bool $interlock, bool $favorRotate, bool $allowMirror): array
    {
        $rotations = $favorRotate ? [90, 270, 0, 180] : [0, 90, 180, 270];
        $baseArea = $this->geometry->polygonArea($shapePoints);
        $placements = [];

        $cursorY = 0.0;
        while ($cursorY < $sheetH) {
            $rowPlaced = false;
            $cursorX = 0.0;
            $rowMaxHeight = 0.0;
            $rowIndex = count($placements) > 0 ? (int) floor($cursorY / max(1.0, $gap)) : 0;

            while ($cursorX < $sheetW) {
                $candidatePlaced = false;
                foreach ($rotations as $deg) {
                    $shape = $this->geometry->rotate($shapePoints, $deg);
                    if ($interlock && ($rowIndex % 2 === 1)) {
                        $shape = $this->geometry->rotate($shape, 180);
                    }
                    if ($allowMirror && ($rowIndex % 2 === 1)) {
                        $shape = $this->geometry->mirrorX($shape);
                    }

                    $bbox = $this->geometry->boundingBox($shape);
                    $tx = $cursorX;
                    $ty = $cursorY;
                    $translated = $this->geometry->translate($shape, $tx, $ty);
                    $tb = $this->geometry->boundingBox($translated);

                    if ($tb['max_x'] > $sheetW || $tb['max_y'] > $sheetH) {
                        continue;
                    }

                    $collides = false;
                    foreach ($placements as $placed) {
                        if ($this->geometry->intersects($translated, $placed['points'])) {
                            $collides = true;
                            break;
                        }
                    }

                    if ($collides) {
                        continue;
                    }

                    $placements[] = [
                        'x_mm' => round($tx, 3),
                        'y_mm' => round($ty, 3),
                        'rotation' => $deg,
                        'points' => $translated,
                    ];
                    $cursorX += $bbox['width'] + $gap;
                    $rowMaxHeight = max($rowMaxHeight, $bbox['height']);
                    $candidatePlaced = true;
                    $rowPlaced = true;
                    break;
                }

                if (! $candidatePlaced) {
                    $cursorX += max(2.0, $gap);
                }
            }

            if (! $rowPlaced) {
                break;
            }
            $cursorY += max(2.0, $rowMaxHeight + $gap);
        }

        return [
            'layout_mode' => $interlock ? 'interlocked' : ($favorRotate ? 'rotated' : 'normal'),
            'placements' => $placements,
            'box_count' => count($placements),
            'used_area_mm2' => $baseArea * count($placements),
        ];
    }
}
