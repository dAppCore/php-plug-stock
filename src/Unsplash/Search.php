<?php

declare(strict_types=1);

namespace Core\Plug\Stock\Unsplash;

use Core\Plug\Concern\BuildsResponse;
use Core\Plug\Concern\UsesHttp;
use Core\Plug\Response;
use Illuminate\Support\Arr;

/**
 * Search Unsplash photos.
 *
 * Usage:
 *   $results = (new Search())->query('social media');
 *   $results = (new Search())->query('', page: 2); // random photos
 */
class Search
{
    use BuildsResponse;
    use UsesHttp;

    protected string $endpointUrl = 'https://api.unsplash.com';

    protected string $clientId;

    /**
     * Default search terms for random photos when no query is provided.
     */
    protected array $defaultSearchTerms = [
        'social',
        'content',
        'marketing',
        'business',
        'technology',
        'creative',
        'lifestyle',
        'professional',
        'minimal',
        'modern',
    ];

    public function __construct()
    {
        $this->clientId = config('services.unsplash.client_id', '');

        if (empty($this->clientId)) {
            throw Exception::notConfigured();
        }
    }

    /**
     * Search for photos.
     *
     * @param  string  $query  Search query (uses random term if empty)
     * @param  int  $page  Page number
     * @param  int  $perPage  Results per page (max 30)
     */
    public function query(string $query = '', int $page = 1, int $perPage = 30): Response
    {
        $searchQuery = ! empty($query)
            ? $query
            : Arr::random($this->defaultSearchTerms);

        $response = $this->http()->get("{$this->endpointUrl}/search/photos", [
            'client_id' => $this->clientId,
            'query' => $searchQuery,
            'page' => $page,
            'per_page' => min($perPage, 30),
        ]);

        return $this->fromHttp($response, function (array $data) {
            $results = $data['results'] ?? [];

            return [
                'photos' => array_map([Photo::class, 'transform'], $results),
                'total' => $data['total'] ?? 0,
                'total_pages' => $data['total_pages'] ?? 0,
            ];
        });
    }
}
