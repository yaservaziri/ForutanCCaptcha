<?php

namespace Forutan\CCaptcha\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CCaptchaPrepareImages extends Command
{
    /**
     * The console command signature.
     */
    protected $signature = 'ccaptcha:prepare-images
        {--from= : Path to source image directory (defaults to package seed images)}';

    /**
     * The console command description.
     */
    protected $description = 'Prepares Circle Captcha background images: removes metadata, resizes them, and stores them for production use.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = $this->option('from') ?? __DIR__ . '/../../assets/backgrounds';
        $outputPath = config('ccaptcha.storage_path', storage_path('app/private/ccaptcha'));
        $width = config('ccaptcha.image_width', 500);
        $height = config('ccaptcha.image_height', 400);
        $quality = config('ccaptcha.image_quality', 90);

        $this->info("Scanning source directory: $from");

        if (!File::exists($from)) {
            $this->error("Source directory not found: $from");
            return 1;
        }

        if (!trim(shell_exec('which convert'))) {
            $this->error('ImageMagick (convert) command not found. Please install it via: sudo apt install imagemagick');
            return 1;
        }

        $this->warn('Cleaning old images...');
        File::deleteDirectory($outputPath);
        File::ensureDirectoryExists($outputPath);

        $images = File::files($from);
        if (empty($images)) {
            $this->warn('No image files found in source directory.');
            return 0;
        }

        $this->info('Processing and optimizing images...');
        $count = 0;

        foreach ($images as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $this->line("Skipped non-image file: {$file->getFilename()}");
                continue;
            }

            $filename = Str::uuid() . '.jpg';
            $target = $outputPath . DIRECTORY_SEPARATOR . $filename;

            $cmd = sprintf(
                'convert %s -resize %dx%d! -strip -interlace Plane -quality %d %s',
                escapeshellarg($file->getRealPath()),
                $width,
                $height,
                $quality,
                escapeshellarg($target)
            );

            exec($cmd, $out, $code);

            if ($code !== 0) {
                $this->error("Failed to process: {$file->getFilename()}");
                continue;
            }

            $this->line("Processed: {$filename}");
            $count++;
        }

        $this->newLine();
        $this->info("Done! {$count} image(s) cleaned, resized, and stored successfully.");
        $this->comment("Output directory: {$outputPath}");

        return 0;
    }
}

//USAGE:
//php artisan ccaptcha:prepare-images
//php artisan ccaptcha:prepare-images --from=path/to/backgrounds