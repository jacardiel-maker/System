<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Builds the Tailwind stylesheets the layouts load instead of the (slow) Tailwind CDN:
 *   public/css/tw-app.css   - main layout      public/css/tw-guest.css - login layout
 * Run it again after changing any view:  php artisan css:build
 * (If you forget, the pages still look right: the layouts fall back to the CDN until the next build.)
 */
Artisan::command('css:build', function () {
    $cli = base_path('node_modules/tailwindcss/lib/cli.js');
    if (!is_file($cli)) {
        $this->error('Tailwind is not installed. Run "npm install" first.');
        return 1;
    }

    if (!is_dir(public_path('css'))) {
        mkdir(public_path('css'), 0755, true);
    }

    foreach (['app', 'guest'] as $name) {
        $process = new Symfony\Component\Process\Process([
            'node', $cli,
            '-c', base_path("tailwind.{$name}.config.js"),
            '-i', resource_path('css/app.css'),
            '-o', public_path("css/tw-{$name}.css"),
            '--minify',
        ], base_path());
        $process->setTimeout(300)->run();

        if (!$process->isSuccessful()) {
            $this->error("Could not build tw-{$name}.css:");
            $this->line($process->getErrorOutput() ?: $process->getOutput());
            return 1;
        }

        $this->info("Built public/css/tw-{$name}.css (" . round(filesize(public_path("css/tw-{$name}.css")) / 1024) . ' KB)');
    }

    // Remember which version of the views these files were built from
    App\Support\StaticCss::flush();
    file_put_contents(public_path(App\Support\StaticCss::MANIFEST), json_encode([
        'views' => App\Support\StaticCss::fingerprint(),
        'built_at' => now()->toDateTimeString(),
    ], JSON_PRETTY_PRINT));

    $this->info('Done. The pages now load the pre-built CSS instead of the Tailwind CDN.');
    return 0;
})->purpose('Build the Tailwind CSS files so pages do not compile styles in the browser');
