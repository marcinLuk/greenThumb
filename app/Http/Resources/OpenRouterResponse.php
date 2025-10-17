<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenRouterResponse extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'] ?? '',
            'model' => $this->resource['model'] ?? '',
            'content' => $this->resource['content'] ?? '',
            'usage' => $this->resource['usage'] ?? [],
            'finish_reason' => $this->resource['finish_reason'] ?? '',
            'raw' => $this->resource['raw'] ?? [],
        ];
    }

    public function isJson(): bool
    {
        return is_array($this->resource['content'] ?? null);
    }

    public function getTokensUsed(): int
    {
        return $this->resource['usage']['total_tokens'] ?? 0;
    }

    public function getContent(): string|array
    {
        return $this->resource['content'] ?? '';
    }

    public function getId(): string
    {
        return $this->resource['id'] ?? '';
    }

    public function getModel(): string
    {
        return $this->resource['model'] ?? '';
    }

    public function getFinishReason(): string
    {
        return $this->resource['finish_reason'] ?? '';
    }

    public function getUsage(): array
    {
        return $this->resource['usage'] ?? [];
    }
}
