<?php
require 'vendor/autoload.php';

$fontPath = storage_path('fonts/IskoolaPota.ttf'); // path to your font
TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
echo "Font conversion done!";
