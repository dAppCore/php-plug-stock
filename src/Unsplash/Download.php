<?php

declare(strict_types=1);

namespace Core\Plug\Stock\Unsplash;

use Core\Plug\Concern\BuildsResponse;
use Core\Plug\Concern\UsesHttp;
use Core\Plug\Response;

/**
 * Trigger Unsplash download tracking.
 *
 * Required by Unsplash API guidelines when downloading a photo.
 * This doesn't download the image - it records the download event.
 *
 * Usage:
 *   (new Download())->trigger($downloadUrl);
 */
class Download
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
     * Trigger download tracking for a photo.
     *
     * @param  string  $downloadLocation  The download_location URL from photo data
     */
    public function trigger(string $downloadLocation): Response
    {
        if (empty($downloadLocation)) {
            return $this->error('Download location URL is required');
        }

        $downloadPath = parse_url($downloadLocation, PHP_URL_PATH);
        $downloadQuery = parse_url($downloadLocation, PHP_URL_QUERY);

        if (! $downloadPath) {
            return $this->error('Invalid download URL');
        }

        $url = "{$this->endpointUrl}{$downloadPath}";
        if ($downloadQuery) {
            $url .= "?{$downloadQuery}";
        }

        $response = $this->http()->get($url, [
            'client_id' => $this->clientId,
        ]);

        return $this->fromHttp($response);
    }
}
