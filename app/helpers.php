<?php declare(strict_types=1);

if (function_exists('getAppVersion') === false) {
    function getAppVersion(): string
    {
        $defaultVersion = 'unknown';

        try {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true, 512, JSON_THROW_ON_ERROR);

            return $composer['version'] ?? $defaultVersion;
        } catch (\JsonException) {
        }

        return $defaultVersion;
    }
}
