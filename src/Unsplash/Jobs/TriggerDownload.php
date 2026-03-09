<?php

declare(strict_types=1);

namespace Core\Plug\Stock\Unsplash\Jobs;

use Core\Plug\Stock\Unsplash\Download;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Async download tracking for Unsplash.
 *
 * Queues the download tracking call to avoid blocking the main request.
 *
 * Usage:
 *   TriggerDownload::dispatch($downloadUrl);
 */
class TriggerDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly string $downloadLocation
    ) {}

    public function handle(): void
    {
        (new Download)->trigger($this->downloadLocation);
    }

    public function tags(): array
    {
        return ['unsplash', 'download-tracking'];
    }
}
