<?php

declare(strict_types=1);

namespace Core\Plug\Stock\Unsplash;

/**
 * Unsplash-specific exception.
 */
class Exception extends \RuntimeException
{
    public static function notConfigured(): self
    {
        return new self(
            'Unsplash is not configured. Add UNSPLASH_CLIENT_ID to your environment configuration.'
        );
    }

    public static function photoNotFound(string $photoId): self
    {
        return new self(
            "Photo with ID '{$photoId}' could not be found on Unsplash."
        );
    }

    public static function apiError(string $message, int $statusCode): self
    {
        return new self(
            "Unsplash API error (HTTP {$statusCode}): {$message}"
        );
    }

    public static function invalidDownloadUrl(string $url): self
    {
        return new self(
            "Invalid Unsplash download URL: {$url}"
        );
    }
}
