<?php

return [
    'advance_payment_percent' => (float) env('PRINTING_ADVANCE_PAYMENT_PERCENT', 50),
    'wastage' => [
        'default_per_color_percent' => (float) env('PRINTING_WASTAGE_PER_COLOR_PERCENT', 2),
        'minimum_percent' => (float) env('PRINTING_WASTAGE_MIN_PERCENT', 5),
        'maximum_percent' => (float) env('PRINTING_WASTAGE_MAX_PERCENT', 8),
    ],
    'inventory' => [
        'low_stock_sheet_threshold' => (int) env('PRINTING_LOW_STOCK_SHEET_THRESHOLD', 2500),
    ],
];
