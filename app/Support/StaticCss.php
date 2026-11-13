<?php

namespace App\Support;

use Symfony\Component\Finder\Finder;

/**
 * Pre-built Tailwind stylesheets (public/css/tw-*.css), made by `php artisan css:build`.
 *
 * Loading Tailwind from its CDN makes the BROWSER compile the styles on every page load, which is
 * slow (and needs internet). A pre-built file is just a small CSS file the browser can cache.
 *
 * Safety net: the build remembers a fingerprint of all the views. If a view was edited after the
 * last build (so the file might be missing new classes), url() returns null and the layout falls
 * back to the CDN. The page then still looks right, only slower, until css:build is run again.
 */
class StaticCss
{
    public const MANIFEST = 'css/tailwind.json';

    private static ?string $fingerprint = null;

    /** Public URL of the pre-built stylesheet ("app" or "guest"), or null when it is missing or out of date. */
    public static function url(string $name): ?string
    {
        $file = public_path("css/tw-{$name}.css");
        $manifest = public_path(self::MANIFEST);

        if (!is_file($file) || !is_file($manifest)) {
            return null;
        }

        $built = json_decode((string) @file_get_contents($manifest), true);
        if (!is_array($built) || ($built['views'] ?? null) !== self::fingerprint()) {
            return null;
        }

        return asset("css/tw-{$name}.css") . '?v=' . substr($built['views'], 0, 10);
    }

    /** Fingerprint of everything the stylesheets are built from (the views and the Tailwind configs). */
    public static function fingerprint(): string
    {
        if (self::$fingerprint !== null) {
            return self::$fingerprint;
        }

        $files = [];
        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            $files[] = $file->getPathname();
        }
        foreach (['tailwind.app.config.js', 'tailwind.guest.config.js', 'resources/css/app.css'] as $extra) {
            if (is_file(base_path($extra))) {
                $files[] = base_path($extra);
            }
        }
        sort($files);

        // Cheap check first (sizes + modified times); the contents are only re-read when that changes
        $stat = '';
        foreach ($files as $path) {
            $stat .= $path . '|' . filesize($path) . '|' . filemtime($path) . "\n";
        }
        $statKey = md5($stat);

        $cacheFile = storage_path('framework/cache/static-css-fingerprint.php');
        $cached = is_file($cacheFile) ? @include $cacheFile : null;
        if (is_array($cached) && ($cached['stat'] ?? null) === $statKey && isset($cached['hash'])) {
            return self::$fingerprint = $cached['hash'];
        }

        // Line endings are ignored so the fingerprint is the same on every computer (Git may convert them)
        $hash = hash_init('md5');
        foreach ($files as $path) {
            $relative = str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
            hash_update($hash, $relative . "\n" . str_replace("\r", '', (string) file_get_contents($path)) . "\n");
        }
        $fingerprint = hash_final($hash);

        @file_put_contents($cacheFile, '<?php return ' . var_export(['stat' => $statKey, 'hash' => $fingerprint], true) . ';', LOCK_EX);

        return self::$fingerprint = $fingerprint;
    }

    /** Forget the remembered fingerprint (used right after a build). */
    public static function flush(): void
    {
        self::$fingerprint = null;
        @unlink(storage_path('framework/cache/static-css-fingerprint.php'));
    }
}
