<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Dompdf\Dompdf;

class DompdfServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Get the Dompdf instance
        $dompdf = new Dompdf();
        $fontMetrics = $dompdf->getFontMetrics();

        // Define font paths
        $fontDir = public_path('fonts');
        $regularFont = $fontDir . '/NotoSansSinhala-Regular.ttf';
        $boldFont    = $fontDir . '/NotoSansSinhala-Bold.ttf';

        // Register the fonts with Dompdf
        $fontMetrics->registerFont([
            'family' => 'NotoSansSinhala',
            'style'  => 'normal',
            'weight' => 'normal',
        ], $regularFont);

        $fontMetrics->registerFont([
            'family' => 'NotoSansSinhala',
            'style'  => 'normal',
            'weight' => 'bold',
        ], $boldFont);

        // Set the default font for all Dompdf instances
        $dompdf->set_option('defaultFont', 'NotoSansSinhala');
    }
}