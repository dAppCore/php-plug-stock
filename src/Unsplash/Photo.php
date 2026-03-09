<?php

declare(strict_types=1);

namespace Core\Plug\Stock\Unsplash;

use Core\Plug\Concern\BuildsResponse;
use Core\Plug\Concern\UsesHttp;
use Core\Plug\Response;

/**
 * Get and represent Unsplash photos.
 *
 * Usage:
 *   $photo = (new Photo())->get('abc123');
 *   $data = Photo::fromApiResponse($apiData);
 */
class Photo
{
    use BuildsResponse;
    use UsesHttp;

    protected string $endpointUrl = 'https://api.unsplash.com';

    protected string $clientId;

    public function __construct()
    {
        $this->clientId = config('services.unsplash.client_id', '');

        if (empty($this->clientId)) {
            throw Exception::notConfigured();
        }
    }

    /**
     * Get a single photo by ID.
     */
    public function get(string $photoId): Response
    {
        $response = $this->http()->get("{$this->endpointUrl}/photos/{$photoId}", [
            'client_id' => $this->clientId,
        ]);

        return $this->fromHttp($response, fn (array $data) => self::transform($data));
    }

    /**
     * Transform API response to standardised format.
     */
    public static function transform(array $data): array
    {
        return [
            'id' => $data['id'] ?? '',
            'description' => $data['description'] ?? '',
            'alt_description' => $data['alt_description'] ?? '',
            'width' => $data['width'] ?? 0,
            'height' => $data['height'] ?? 0,
            'aspect_ratio' => self::calculateAspectRatio($data['width'] ?? 0, $data['height'] ?? 0),
            'urls' => $data['urls'] ?? [],
            'links' => $data['links'] ?? [],
            'user' => $data['user'] ?? [],
            'likes' => $data['likes'] ?? 0,
            'color' => $data['color'] ?? '#000000',
            'created_at' => $data['created_at'] ?? '',
            'download_url' => $data['links']['download_location'] ?? '',
            'photographer_name' => $data['user']['name'] ?? 'Unknown',
            'photographer_url' => $data['user']['links']['html'] ?? '',
            'attribution' => self::buildAttribution($data),
        ];
    }

    /**
     * Build attribution text.
     */
    public static function buildAttribution(array $data): string
    {
        $name = $data['user']['name'] ?? 'Unknown';

        return "Photo by {$name} on Unsplash";
    }

    /**
     * Calculate aspect ratio.
     */
    protected static function calculateAspectRatio(int $width, int $height): float
    {
        if ($height === 0) {
            return 1.0;
        }

        return round($width / $height, 4);
    }
}
