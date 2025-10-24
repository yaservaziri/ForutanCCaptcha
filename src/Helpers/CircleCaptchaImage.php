<?php

namespace Forutan\CCaptcha\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class CircleCaptchaImage
{
    public static function generate(int $width = 500, int $height = 400, int $circleCount = 12, int $rMin = 20, int $rMax = 35): array
    {
        $backgroundPath = storage_path('app/private/ccaptcha');

        $backgroundFiles = Cache::remember('captcha_backgrounds', now()->addHours(1), function () use ($backgroundPath) {
            return collect(File::files($backgroundPath))->map->getPathname()->toArray();
        });

        if (empty($backgroundFiles)) {
            abort(500, 'No background images found.');
        }

        $randomBackground = $backgroundFiles[array_rand($backgroundFiles)];

        $im = self::createImageFromBackground($randomBackground, $width, $height);
        if (!$im) {
            abort(500, 'Failed to load background image.');
        }

        $noiseColor = imagecolorallocatealpha($im, rand(100,200), rand(100,200), rand(100,200), 50);
        for ($i = 0; $i < rand(500,1000); $i++) {
            imagesetpixel($im, rand(0,$width), rand(0,$height), $noiseColor);
        }
        for ($i=0; $i < rand(10,15); $i++) {
            imageline($im, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $noiseColor);
        }

        $brokenIndex = rand(0, $circleCount-1);
        $brokenCircle = null;

        for ($i=0; $i<$circleCount; $i++) {
            $radius = rand($rMin, $rMax);
            $x = rand($radius+10, $width-$radius-10);
            $y = rand($radius+10, $height-$radius-10);
            $color = imagecolorallocatealpha($im, rand(60,255), rand(60,255), rand(60,255), rand(20,50));

            if ($i === $brokenIndex) {
                imagesetthickness($im, rand(1,2));
                $startAngle = rand(0,360);
                $endAngle = ($startAngle + rand(290,320)) % 360;
                $brokenColor = imagecolorallocate($im, rand(60,255), rand(60,255), rand(20,50));
                imagearc($im, $x, $y, $radius*2, $radius*2, $startAngle, $endAngle, $brokenColor);
                $brokenCircle = ['x'=>$x,'y'=>$y,'r'=>$radius];
            } else {
                imagearc($im, $x, $y, $radius*2, $radius*2, 0, 360, $color);
            }
        }

        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);
        $base64 = 'data:image/png;base64,' . base64_encode($imageData);

        return [
            'imageBase64' => $base64,
            'brokenCircle' => $brokenCircle,
        ];
    }

    private static function createImageFromBackground($backgroundPath, $width, $height)
    {
        $ext = strtolower(pathinfo($backgroundPath, PATHINFO_EXTENSION));
        if ($ext === 'png') $im = @imagecreatefrompng($backgroundPath);
        elseif (in_array($ext,['jpg','jpeg'])) $im = @imagecreatefromjpeg($backgroundPath);
        else return false;

        if (!$im) return false;

        $resized = imagecreatetruecolor($width,$height);
        imagecopyresampled($resized, $im, 0,0,0,0, $width,$height, imagesx($im), imagesy($im));
        imagedestroy($im);

        return $resized;
    }
}
