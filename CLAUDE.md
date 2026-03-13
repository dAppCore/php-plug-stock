# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

`lthn/php-plug-stock` is a PHP library providing stock imagery provider integrations for the Plug framework. Currently implements Unsplash as the sole provider. Licensed under EUPL-1.2.

## Dependencies

- PHP ^8.2
- `lthn/php` (core Plug framework) — provides `Core\Plug\Concern\BuildsResponse`, `Core\Plug\Concern\UsesHttp`, and `Core\Plug\Response`
- Laravel (used for `config()`, HTTP client, queue system)

## Commands

```bash
composer install          # Install dependencies
```

No test suite, linter, or build step is configured in this repository.

## Architecture

**Namespace:** `Core\Plug\Stock\` mapped to `src/` via PSR-4.

All provider classes live under a provider subdirectory (e.g. `src/Unsplash/`). Each class follows the same pattern:

- Constructor reads config via `config('services.unsplash.client_id')` and throws `Exception::notConfigured()` if missing
- Uses traits `BuildsResponse` and `UsesHttp` from the core Plug framework for HTTP calls and standardised `Response` objects
- `Photo::transform()` is the shared normaliser — used by `Search`, `Collection`, and `Photo` to produce a consistent photo data structure

**Key classes:**
- `Search` — keyword search with fallback to random default terms
- `Photo` — single photo retrieval + static `transform()` normaliser
- `Collection` — list collections and their photos
- `Download` — triggers Unsplash download tracking (required by API guidelines)
- `Jobs\TriggerDownload` — Laravel queued job wrapping `Download` (3 retries, 60s backoff)
- `Exception` — named constructors for domain-specific errors

## Configuration

Requires `UNSPLASH_CLIENT_ID` environment variable, accessed via `config('services.unsplash.client_id')`.
