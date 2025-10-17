<?php

declare(strict_types=1);

namespace App\Exceptions\OpenRouter;

use Exception;

class OpenRouterException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        protected array $context = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
