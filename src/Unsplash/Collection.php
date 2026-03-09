<?php

declare(strict_types=1);

namespace Core\Plug\Stock\Unsplash;

use Core\Plug\Concern\BuildsResponse;
use Core\Plug\Concern\UsesHttp;
use Core\Plug\Response;

/**
 * Unsplash collections.
 *
 * Usage:
 *   $collections = (new Collection())->list();
 *   $photos = (new Collection())->photos('abc123');
 */
class Collection
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
     * Get curated collections.
     */
    public function list(int $page = 1, int $perPage = 30): Response
    {
        $response = $this->http()->get("{$this->endpointUrl}/collections", [
            'client_id' => $this->clientId,
            'page' => $page,
            'per_page' => min($perPage, 30),
        ]);

        return $this->fromHttp($response, fn (array $data) => [
            'collections' => array_map(fn ($c) => [
                'id' => $c['id'] ?? '',
                'title' => $c['title'] ?? '',
                'description' => $c['description'] ?? '',
                'total_photos' => $c['total_photos'] ?? 0,
                'cover_photo' => isset($c['cover_photo']) ? Photo::transform($c['cover_photo']) : null,
            ], $data),
        ]);
    }

    /**
     * Get photos from a collection.
     */
    public function photos(string $collectionId, int $page = 1, int $perPage = 30): Response
    {
        $response = $this->http()->get("{$this->endpointUrl}/collections/{$collectionId}/photos", [
            'client_id' => $this->clientId,
            'page' => $page,
            'per_page' => min($perPage, 30),
        ]);

        return $this->fromHttp($response, fn (array $data) => [
            'photos' => array_map([Photo::class, 'transform'], $data),
        ]);
    }
}
