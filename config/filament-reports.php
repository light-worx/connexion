<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Font Settings
    |--------------------------------------------------------------------------
    */
    'default_font' => [
        'family' => 'Arial',
        'size' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Settings
    |--------------------------------------------------------------------------
    */
    'page' => [
        'orientation' => 'P', // P=Portrait, L=Landscape
        'unit' => 'mm',
        'size' => 'A4',
        'margins' => [
            'top' => 15,
            'right' => 15,
            'bottom' => 15,
            'left' => 15,
        ],
    ],

    'report_paths' => [
        // Main app reports
        [
            'path' => app_path('Reports'),
            'namespace' => 'App\\Reports',
        ],
        
        // Module reports
        [
            'path' => base_path('Modules/*/app/Reports'),
            'namespace' => 'Modules\\{module}\\Reports',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */
    'branding' => [
        'company_name' => env('APP_NAME', 'Your Company'),
        'logo_path' => null, // Path to logo file
        'primary_color' => [0, 0, 0], // RGB values
        'secondary_color' => [128, 128, 128], // RGB values
    ],

    /*
    |--------------------------------------------------------------------------
    | Header & Footer
    |--------------------------------------------------------------------------
    */
    'header' => [
        'enabled' => true,
        'height' => 20,
    ],

    'footer' => [
        'enabled' => true,
        'height' => 15,
        'show_page_numbers' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Defaults
    |--------------------------------------------------------------------------
    */
    'table' => [
        'header_background' => [240, 240, 240], // RGB
        'header_text_color' => [0, 0, 0], // RGB
        'border_color' => [200, 200, 200], // RGB
        'row_height' => 7,
        'header_height' => 9,
        'alternating_rows' => true,
        'alternating_color' => [250, 250, 250], // RGB
    ],
];