<?php

declare(strict_types=1);

namespace App\Services\OpenRouter;

use App\Exceptions\OpenRouter\OpenRouterApiException;
use App\Exceptions\OpenRouter\OpenRouterAuthenticationException;
use App\Exceptions\OpenRouter\OpenRouterInvalidRequestException;
use App\Exceptions\OpenRouter\OpenRouterModelNotSupportedException;
use App\Exceptions\OpenRouter\OpenRouterNetworkException;
use App\Exceptions\OpenRouter\OpenRouterRateLimitException;
use App\Http\Resources\OpenRouterResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class OpenRouterService
{
    private string $defaultModel = 'openai/gpt-4o-mini';

    private float $defaultTemperature = 1.0;

    private int $timeout = 30;

    public string $apiVersion = 'v1';

    public int $defaultTimeout = 30;

    public int $maxRetries = 3;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://openrouter.ai/api/v1',
        private readonly ?LoggerInterface $logger = null
    ) {
        if (empty($this->apiKey)) {
            throw new InvalidArgumentException('OpenRouter API key is required');
        }
    }

    public function chat(
        array $messages,
        string $model,
        ?array $responseFormat = null,
        array $parameters = []
    ): OpenRouterResponse {
        $this->validateMessages($messages);
        $this->validateResponseFormat($responseFormat);
        $this->validateParameters($parameters);

        if (empty($model)) {
            throw new InvalidArgumentException('Model cannot be empty');
        }

        $payload = $this->buildRequestPayload($messages, $model, $responseFormat, $parameters);
        $response = $this->sendRequestWithRetry('/chat/completions', $payload);
        $result = $this->parseResponse($response, $responseFormat !== null);

        $this->logResponse($result);

        return $result;
    }

    public function chatSimple(
        string $userMessage,
        ?string $systemMessage = null,
        string $model = 'openai/gpt-4o-mini'
    ): string {
        $messages = [];

        if ($systemMessage !== null) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = $this->chat($messages, $model);

        return $response->getContent();
    }

    public function chatStructured(
        array $messages,
        string $model,
        string $schemaName,
        array $schema,
        array $parameters = []
    ): array {
        $responseFormat = [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => $schemaName,
                'strict' => true,
                'schema' => $schema,
            ],
        ];

        $response = $this->chat($messages, $model, $responseFormat, $parameters);

        if (! is_array($response->getContent())) {
            throw new OpenRouterApiException(
                'Expected structured response but got string'
            );
        }

        return $response->getContent();
    }

    public function setDefaultModel(string $model): self
    {
        $this->defaultModel = $model;

        return $this;
    }

    public function setDefaultTemperature(float $temperature): self
    {
        if ($temperature < 0 || $temperature > 2) {
            throw new InvalidArgumentException('Temperature must be between 0 and 2');
        }
        $this->defaultTemperature = $temperature;

        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException('Timeout must be positive');
        }
        $this->timeout = $seconds;

        return $this;
    }

    private function validateMessages(array $messages): void
    {
        if (empty($messages)) {
            throw new InvalidArgumentException('Messages array cannot be empty');
        }

        $validRoles = ['system', 'user', 'assistant', 'developer', 'tool'];

        foreach ($messages as $index => $message) {
            if (! is_array($message)) {
                throw new InvalidArgumentException("Message at index $index must be an array");
            }

            if (! isset($message['role'])) {
                throw new InvalidArgumentException("Message at index $index missing 'role' key");
            }

            if (! in_array($message['role'], $validRoles, true)) {
                throw new InvalidArgumentException(
                    "Invalid role '{$message['role']}' at index $index. ".
                    'Must be one of: '.implode(', ', $validRoles)
                );
            }

            if (! isset($message['content']) || ! is_string($message['content']) || trim($message['content']) === '') {
                throw new InvalidArgumentException("Message at index $index missing or empty 'content'");
            }
        }
    }

    private function validateResponseFormat(?array $responseFormat): void
    {
        if ($responseFormat === null) {
            return;
        }

        if (! isset($responseFormat['type']) || $responseFormat['type'] !== 'json_schema') {
            throw new InvalidArgumentException("response_format type must be 'json_schema'");
        }

        if (! isset($responseFormat['json_schema'])) {
            throw new InvalidArgumentException("response_format must contain 'json_schema' key");
        }

        $jsonSchema = $responseFormat['json_schema'];

        if (! isset($jsonSchema['name']) || ! is_string($jsonSchema['name']) || trim($jsonSchema['name']) === '') {
            throw new InvalidArgumentException("json_schema must have non-empty 'name'");
        }

        if (! isset($jsonSchema['strict']) || $jsonSchema['strict'] !== true) {
            throw new InvalidArgumentException("json_schema must have 'strict' set to true");
        }

        if (! isset($jsonSchema['schema']) || ! is_array($jsonSchema['schema'])) {
            throw new InvalidArgumentException("json_schema must contain 'schema' array");
        }

        $schema = $jsonSchema['schema'];

        if (! isset($schema['type']) || $schema['type'] !== 'object') {
            throw new InvalidArgumentException("schema type must be 'object'");
        }

        if (! isset($schema['properties']) || ! is_array($schema['properties'])) {
            throw new InvalidArgumentException("schema must contain 'properties' array");
        }
    }

    private function validateParameters(array $parameters): void
    {
        $validations = [
            'temperature' => fn ($v) => is_numeric($v) && $v >= 0 && $v <= 2,
            'top_p' => fn ($v) => is_numeric($v) && $v >= 0 && $v <= 1,
            'top_k' => fn ($v) => is_int($v) && $v > 0,
            'max_tokens' => fn ($v) => is_int($v) && $v > 0,
            'frequency_penalty' => fn ($v) => is_numeric($v) && $v >= -2 && $v <= 2,
            'presence_penalty' => fn ($v) => is_numeric($v) && $v >= -2 && $v <= 2,
            'seed' => fn ($v) => is_int($v),
            'stream' => fn ($v) => is_bool($v),
        ];

        foreach ($parameters as $key => $value) {
            if (isset($validations[$key]) && ! $validations[$key]($value)) {
                throw new InvalidArgumentException("Invalid value for parameter '$key'");
            }
        }
    }

    private function buildRequestPayload(
        array $messages,
        string $model,
        ?array $responseFormat,
        array $parameters
    ): array {
        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;
        }

        return array_merge($payload, $parameters);
    }

    private function sendRequest(string $endpoint, array $payload): array
    {
        $url = $this->baseUrl.$endpoint;

        $this->logRequest($endpoint, $payload);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name'),
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            $this->handleErrorResponse($response->status(), $response->json());
        } catch (ConnectionException $e) {
            throw new OpenRouterNetworkException(
                'Network error: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    private function sendRequestWithRetry(string $endpoint, array $payload): array
    {
        $attempt = 0;
        $maxRetries = $this->maxRetries;

        while ($attempt < $maxRetries) {
            try {
                return $this->sendRequest($endpoint, $payload);
            } catch (OpenRouterNetworkException|OpenRouterApiException $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }

                $waitTime = pow(2, $attempt - 1);
                sleep($waitTime);

                $this->logger?->warning("Retrying OpenRouter request (attempt $attempt)", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw new OpenRouterApiException('Failed to send request after '.$maxRetries.' attempts');
    }

    private function handleErrorResponse(int $statusCode, array $error): never
    {
        $message = $error['error']['message'] ?? 'Unknown error';

        match ($statusCode) {
            401 => throw new OpenRouterAuthenticationException(
                'Authentication failed: '.$message,
                401
            ),
            429 => throw new OpenRouterRateLimitException(
                'Rate limit exceeded: '.$message,
                429
            ),
            400 => throw str_contains($message, 'does not support')
                ? new OpenRouterModelNotSupportedException($message, 400)
                : new OpenRouterInvalidRequestException($message, 400),
            default => throw new OpenRouterApiException(
                'API error: '.$message,
                $statusCode
            ),
        };
    }

    private function parseResponse(array $response, bool $expectJson = false): OpenRouterResponse
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        if ($expectJson && is_string($content)) {
            $content = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new OpenRouterApiException(
                    'Failed to parse JSON response: '.json_last_error_msg()
                );
            }
        }

        return new OpenRouterResponse([
            'id' => $response['id'] ?? '',
            'model' => $response['model'] ?? '',
            'content' => $content,
            'usage' => $response['usage'] ?? [],
            'finish_reason' => $response['choices'][0]['finish_reason'] ?? '',
            'raw' => $response,
        ]);
    }

    private function logRequest(string $endpoint, array $payload): void
    {
        $this->logger?->debug('OpenRouter API Request', [
            'endpoint' => $endpoint,
            'model' => $payload['model'] ?? null,
            'message_count' => count($payload['messages'] ?? []),
            'has_response_format' => isset($payload['response_format']),
        ]);
    }

    private function logResponse(OpenRouterResponse $response): void
    {
        $this->logger?->debug('OpenRouter API Response', [
            'id' => $response->getId(),
            'model' => $response->getModel(),
            'finish_reason' => $response->getFinishReason(),
            'tokens_used' => $response->getTokensUsed(),
        ]);
    }
}
