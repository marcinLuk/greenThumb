# OpenRouter Service Implementation Plan

## 1. Service Description

The OpenRouter service is a PHP-based HTTP client wrapper that interacts with the OpenRouter API (https://openrouter.ai/api/v1/chat/completions) to perform LLM-based chat completions. 
The service provides a clean, Laravel-idiomatic interface for sending messages to various AI models, receiving responses (both streamed and non-streamed), 
and handling structured JSON outputs via JSON Schema validation.

### Purpose
- Abstracts OpenRouter API communication details
- Provides type-safe message composition (system, user, assistant roles)
- Supports structured responses using JSON Schema
- Handles authentication, error management, and HTTP communication
- Enables streaming responses for real-time output
- Integrates seamlessly with Laravel's service container

### Key Capabilities
- Send chat completion requests with configurable model parameters
- Define system prompts and user messages
- Enforce structured JSON responses using json_schema
- Handle both synchronous and streaming responses
- Manage API authentication securely
- Provide comprehensive error handling and logging

---

## 2. Constructor Description

### Parameters

1. **$apiKey** (string, required)
   - OpenRouter API authentication token
   - Retrieved from environment configuration (`OPENROUTER_API_KEY`)
   - Used in Authorization header as `Bearer {$apiKey}`
   - Should never be hardcoded or logged

2. **$baseUrl** (string, optional)
   - Base URL for OpenRouter API
   - Default: `https://openrouter.ai/api/v1`
   - Allows override for testing (mock servers)
   - Should end without trailing slash

3. **$httpClient** (HttpClient, optional)
   - HTTP client implementation for API requests
   - Defaults to Laravel's HTTP facade if null
   - Enables dependency injection for testing
   - Should support timeout, retry logic, and streaming

### Constructor Responsibilities
- Validate API key is not empty
- Initialize HTTP client with default timeout (30s) and retry logic (3 attempts)
- Configure logger with appropriate context
- Throw `InvalidArgumentException` if API key is missing

---

## 3. Public Methods and Fields

### 3.1 Primary Method: `chat()`

**Purpose**: Execute a chat completion request with full control over messages, model, response format, and parameters.

**Parameters:**

1. **$messages** (array, required)
   - Array of message objects with `role` and `content` keys
   - Supported roles: `system`, `user`, `assistant`, `developer`, `tool`
   - Example:
     ```php
     [
         ['role' => 'system', 'content' => 'You are a helpful assistant.'],
         ['role' => 'user', 'content' => 'What is the weather?']
     ]
     ```
   - Validation: Each message must have valid role and non-empty content

2. **$model** (string, required)
   - Model identifier (e.g., `openai/gpt-4`, `anthropic/claude-3-opus`)
   - See OpenRouter models page for available models
   - Example models:
     - `openai/gpt-4-turbo`
     - `anthropic/claude-3-5-sonnet`
     - `google/gemini-pro`
   - Validation: Must be non-empty string

3. **$responseFormat** (array|null, optional)
   - Controls structured output using JSON Schema
   - When null: Model returns natural language response
   - When provided: Must follow this exact structure:
     ```php
     [
         'type' => 'json_schema',
         'json_schema' => [
             'name' => 'schema_name',
             'strict' => true,
             'schema' => [
                 'type' => 'object',
                 'properties' => [
                     'field1' => ['type' => 'string', 'description' => '...'],
                     'field2' => ['type' => 'number', 'description' => '...']
                 ],
                 'required' => ['field1', 'field2'],
                 'additionalProperties' => false
             ]
         ]
     ]
     ```
   - **Important**: Always set `strict: true` and `additionalProperties: false`
   - Validation: Verify schema structure before API call

4. **$parameters** (array, optional)
   - Additional model parameters
   - Supported parameters:
     - `temperature` (float, 0-2): Controls randomness (default: 1.0)
     - `max_tokens` (int): Maximum response length
     - `top_p` (float, 0-1): Nucleus sampling threshold
     - `top_k` (int): Top-k sampling parameter
     - `frequency_penalty` (float, -2 to 2): Reduce repetition
     - `presence_penalty` (float, -2 to 2): Encourage topic diversity
     - `seed` (int): For reproducible outputs
     - `stream` (bool): Enable streaming (default: false)
   - Example:
     ```php
     [
         'temperature' => 0.7,
         'max_tokens' => 1000,
         'stream' => false
     ]
     ```

**Returns**: `OpenRouterResponse` object containing:
- `id`: Request identifier
- `model`: Model used
- `content`: Response content (string or parsed JSON)
- `usage`: Token usage statistics
- `finishReason`: Completion status

**Throws**:
- `InvalidArgumentException`: Invalid parameters
- `OpenRouterAuthenticationException`: Authentication failure
- `OpenRouterRateLimitException`: Rate limit exceeded
- `OpenRouterApiException`: General API errors

---

### 3.2 Convenience Methods

#### `chatSimple()`

```php
public function chatSimple(
    string $userMessage,
    ?string $systemMessage = null,
    string $model = 'openai/gpt-3.5-turbo'
): string
```

**Purpose**: Simplified interface for basic chat completion.

**Example**:
```php
$response = $service->chatSimple(
    'What is 2+2?',
    'You are a math tutor',
    'openai/gpt-4'
);
// Returns: "4"
```

---

#### `chatStructured()`

```php
public function chatStructured(
    array $messages,
    string $model,
    string $schemaName,
    array $schema,
    array $parameters = []
): array
```

**Purpose**: Simplified interface for structured JSON responses.

**Example**:
```php
$result = $service->chatStructured(
    [
        ['role' => 'user', 'content' => 'What is the weather in Paris?']
    ],
    'openai/gpt-4',
    'weather_response',
    [
        'type' => 'object',
        'properties' => [
            'location' => ['type' => 'string', 'description' => 'City name'],
            'temperature' => ['type' => 'number', 'description' => 'Temperature in Celsius'],
            'conditions' => ['type' => 'string', 'description' => 'Weather conditions']
        ],
        'required' => ['location', 'temperature', 'conditions'],
        'additionalProperties' => false
    ]
);

// Returns: ['location' => 'Paris', 'temperature' => 18, 'conditions' => 'Sunny']
```

### 3.3 Configuration Methods

#### `setDefaultModel()`

```php
public function setDefaultModel(string $model): self
```

**Purpose**: Set default model for subsequent requests.

---

#### `setDefaultTemperature()`

```php
public function setDefaultTemperature(float $temperature): self
```

**Purpose**: Set default temperature (0-2 range).

---

#### `setTimeout()`

```php
public function setTimeout(int $seconds): self
```

**Purpose**: Configure HTTP request timeout.

---

### 3.4 Public Fields

```php
public readonly string $apiVersion = 'v1';
public readonly int $defaultTimeout = 30;
public readonly int $maxRetries = 3;
```

---

## 4. Private Methods and Fields

### 4.1 Private Fields

```php
private string $defaultModel = 'openai/gpt-3.5-turbo';
private float $defaultTemperature = 1.0;
private int $timeout = 30;
private array $defaultHeaders = [];
```

---

### 4.2 Private Methods

#### `buildRequestPayload()`

```php
private function buildRequestPayload(
    array $messages,
    string $model,
    ?array $responseFormat,
    array $parameters
): array
```

**Purpose**: Construct the complete request body for OpenRouter API.

**Logic**:
1. Start with base payload: `['model' => $model, 'messages' => $messages]`
2. Add `response_format` if provided
3. Merge additional parameters (temperature, max_tokens, etc.)
4. Validate final payload structure
5. Return associative array

**Example Output**:
```php
[
    'model' => 'openai/gpt-4',
    'messages' => [
        ['role' => 'system', 'content' => 'You are helpful.'],
        ['role' => 'user', 'content' => 'Hello']
    ],
    'response_format' => [
        'type' => 'json_schema',
        'json_schema' => [...]
    ],
    'temperature' => 0.7,
    'max_tokens' => 1000
]
```

---

#### `validateMessages()`

```php
private function validateMessages(array $messages): void
```

**Purpose**: Ensure messages array is properly formatted.

**Validation Rules**:
1. Array must not be empty
2. Each element must be an array
3. Each message must have `role` key
4. Each message must have `content` key
5. Role must be one of: `system`, `user`, `assistant`, `developer`, `tool`
6. Content must be non-empty string

**Throws**: `InvalidArgumentException` with descriptive error message.

---

#### `validateResponseFormat()`

```php
private function validateResponseFormat(?array $responseFormat): void
```

**Purpose**: Validate response_format structure.

**Validation Rules**:
1. If null, skip validation
2. Must have `type` key with value `json_schema`
3. Must have `json_schema` key containing:
   - `name` (string, non-empty)
   - `strict` (bool, must be true)
   - `schema` (array with valid JSON Schema)
4. Schema must have:
   - `type` = `object`
   - `properties` (array)
   - `required` (array, optional)
   - `additionalProperties` = false (recommended)

**Throws**: `InvalidArgumentException` if validation fails.

---

#### `validateParameters()`

```php
private function validateParameters(array $parameters): void
```

**Purpose**: Validate model parameters.

**Validation Rules**:
1. `temperature`: 0 ≤ value ≤ 2
2. `top_p`: 0 ≤ value ≤ 1
3. `top_k`: integer > 0
4. `max_tokens`: integer > 0
5. `frequency_penalty`: -2 ≤ value ≤ 2
6. `presence_penalty`: -2 ≤ value ≤ 2
7. `seed`: integer
8. `stream`: boolean

**Throws**: `InvalidArgumentException` for out-of-range values.

---

#### `sendRequest()`

```php
private function sendRequest(string $endpoint, array $payload): array
```

**Purpose**: Execute HTTP POST request to OpenRouter API.

**Implementation**:
1. Prepare headers:
   ```php
   [
       'Authorization' => 'Bearer ' . $this->apiKey,
       'Content-Type' => 'application/json',
       'HTTP-Referer' => config('app.url'), // Optional
       'X-Title' => config('app.name') // Optional
   ]
   ```
2. Send POST request to `$baseUrl . $endpoint`
3. Handle HTTP status codes (see error handling section)
4. Parse JSON response
5. Return response array

**Throws**: Various OpenRouter exceptions based on status code.


#### `parseResponse()`

```php
private function parseResponse(array $response): OpenRouterResponse
```

**Purpose**: Convert raw API response to `OpenRouterResponse` DTO.

**Logic**:
1. Extract response ID
2. Get model name
3. Extract message content from `choices[0].message.content`
4. Parse usage statistics (prompt_tokens, completion_tokens, total_tokens)
5. Get finish_reason (stop, length, content_filter, etc.)
6. If response_format was json_schema, decode content as JSON
7. Create and return OpenRouterResponse object


## 5. Error Handling

### 5.1 Exception Hierarchy

```php
OpenRouterException (base)
├── OpenRouterAuthenticationException (401)
├── OpenRouterRateLimitException (429)
├── OpenRouterInvalidRequestException (400)
├── OpenRouterModelNotSupportedException (400 with specific error)
├── OpenRouterApiException (5xx)
└── OpenRouterNetworkException (connection failures)
```

### 5.2 Error Scenarios

#### 1. Authentication Failure (401)
**Cause**: Invalid or missing API key
**Response**:
```json
{
  "error": {
    "code": 401,
    "message": "Invalid authentication credentials"
  }
}
```
**Handling**:
```php
throw new OpenRouterAuthenticationException(
    'Authentication failed: ' . $error['message'],
    401
);
```

---

#### 2. Rate Limit Exceeded (429)
**Cause**: Too many requests in time window
**Response**:
```json
{
  "error": {
    "code": 429,
    "message": "Rate limit exceeded"
  }
}
```
**Handling**:
```php
throw new OpenRouterRateLimitException(
    'Rate limit exceeded. Retry after: ' . $retryAfter,
    429,
    null,
    ['retry_after' => $retryAfter]
);
```

---

#### 3. Invalid Request (400)
**Cause**: Malformed request, invalid parameters, or unsupported model
**Response**:
```json
{
  "error": {
    "code": 400,
    "message": "Invalid request: temperature must be between 0 and 2"
  }
}
```
**Handling**:
```php
// Check if model doesn't support response_format
if (str_contains($error['message'], 'does not support')) {
    throw new OpenRouterModelNotSupportedException($error['message'], 400);
}

throw new OpenRouterInvalidRequestException($error['message'], 400);
```

---

#### 4. Server Error (5xx)
**Cause**: OpenRouter API internal error
**Handling**:
```php
throw new OpenRouterApiException(
    'OpenRouter API error: ' . ($error['message'] ?? 'Unknown error'),
    $statusCode
);
```

---

#### 5. Network Failure
**Cause**: Connection timeout, DNS failure, network interruption
**Handling**:
```php
try {
    // HTTP request
} catch (ConnectionException $e) {
    throw new OpenRouterNetworkException(
        'Network error: ' . $e->getMessage(),
        0,
        $e
    );
}
```

---

#### 6. Invalid JSON Schema
**Cause**: Malformed response_format schema
**Handling**: Throw `InvalidArgumentException` before API call with detailed error message.

---

### 5.3 Retry Logic

Implement exponential backoff for transient errors:

```php
private function sendRequestWithRetry(string $endpoint, array $payload): array
{
    $attempt = 0;
    $maxRetries = $this->maxRetries;

    while ($attempt < $maxRetries) {
        try {
            return $this->sendRequest($endpoint, $payload);
        } catch (OpenRouterNetworkException | OpenRouterApiException $e) {
            $attempt++;
            if ($attempt >= $maxRetries) {
                throw $e;
            }

            // Exponential backoff: 1s, 2s, 4s
            $waitTime = pow(2, $attempt - 1);
            sleep($waitTime);

            $this->logger?->warning("Retrying OpenRouter request (attempt $attempt)", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

**Retry Policy**:
- Retry on: Network errors, 5xx errors
- Do NOT retry: 400, 401, 403, 429
- Max retries: 3
- Backoff: Exponential (1s, 2s, 4s)

---

## 6. Security Considerations

### 6.1 API Key Management

1. **Environment Variables**
   - Store API key in `.env` file
   - Never commit `.env` to version control
   - Use `OPENROUTER_API_KEY=your_key_here`

2. **Configuration**
   ```php
   // config/services.php
   return [
       'openrouter' => [
           'api_key' => env('OPENROUTER_API_KEY'),
           'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
       ],
   ];
   ```

3. **Service Binding**
   ```php
   // app/Providers/AppServiceProvider.php
   $this->app->singleton(OpenRouterService::class, function ($app) {
       return new OpenRouterService(
           apiKey: config('services.openrouter.api_key'),
           baseUrl: config('services.openrouter.base_url'),
           logger: $app->make(LoggerInterface::class)
       );
   });
   ```

---

### 6.2 Input Sanitization

1. **Validate User Input**
   - Sanitize all user-provided content in messages
   - Prevent injection attacks
   - Limit message length (e.g., max 10,000 characters)

2. **Example**:
   ```php
   private function sanitizeContent(string $content): string
   {
       // Remove null bytes
       $content = str_replace("\0", '', $content);

       // Trim whitespace
       $content = trim($content);

       // Enforce length limit
       if (strlen($content) > 10000) {
           throw new InvalidArgumentException('Message content exceeds maximum length');
       }

       return $content;
   }
   ```

---

### 6.3 Logging Security

1. **Redact Sensitive Data**
   ```php
   private function redactApiKey(array $headers): array
   {
       if (isset($headers['Authorization'])) {
           $headers['Authorization'] = 'Bearer [REDACTED]';
       }
       return $headers;
   }
   ```

2. **Avoid Logging User Content**
   - Only log metadata (model, token count, finish_reason)
   - Do not log full message content or responses
   - Comply with data protection regulations (GDPR, etc.)

---

### 6.4 Rate Limiting

1. **Client-Side Rate Limiting**
   - Implement token bucket or leaky bucket algorithm
   - Prevent accidental API abuse
   - Example: Max 100 requests/minute per user

2. **Respect API Rate Limits**
   - Handle 429 responses gracefully
   - Implement exponential backoff
   - Cache responses when appropriate

---

## 7. Step-by-Step Implementation Plan

### Phase 1: Project Setup and Dependencies

#### Step 1.1: Create Service Directory Structure
```bash
mkdir -p app/Services/OpenRouter
mkdir -p app/Exceptions/OpenRouter
mkdir -p app/DataTransferObjects
```

#### Step 1.2: Install HTTP Client (if needed)
Laravel's HTTP facade is sufficient, but ensure Guzzle is installed:
```bash
composer require guzzlehttp/guzzle
```

### Phase 2: Create Exception Classes

#### Step 2.1: Base Exception
Create `app/Exceptions/OpenRouter/OpenRouterException.php`:
```php
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
```

#### Step 2.2: Specific Exceptions
Create the following exception classes extending `OpenRouterException`:
- `OpenRouterAuthenticationException.php`
- `OpenRouterRateLimitException.php`
- `OpenRouterInvalidRequestException.php`
- `OpenRouterModelNotSupportedException.php`
- `OpenRouterApiException.php`
- `OpenRouterNetworkException.php`

---

### Phase 3: Create Data Transfer Object

#### Step 3.1: OpenRouterResponse DTO
Create `app/DataTransferObjects/OpenRouterResponse.php`:
```php
<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

readonly class OpenRouterResponse
{
    public function __construct(
        public string $id,
        public string $model,
        public string|array $content,
        public array $usage,
        public string $finishReason,
        public array $raw
    ) {}

    public function isJson(): bool
    {
        return is_array($this->content);
    }

    public function getTokensUsed(): int
    {
        return $this->usage['total_tokens'] ?? 0;
    }
}
```

---

### Phase 4: Implement Core Service Class

#### Step 4.1: Create OpenRouterService Skeleton
Create `app/Services/OpenRouter/OpenRouterService.php`:
```php
<?php

declare(strict_types=1);

namespace App\Services\OpenRouter;

use App\DataTransferObjects\OpenRouterResponse;
use App\Exceptions\OpenRouter\OpenRouterException;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;

class OpenRouterService
{
    private string $defaultModel = 'openai/gpt-3.5-turbo';
    private float $defaultTemperature = 1.0;
    private int $timeout = 30;

    public readonly string $apiVersion = 'v1';
    public readonly int $defaultTimeout = 30;
    public readonly int $maxRetries = 3;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://openrouter.ai/api/v1',
        private readonly ?LoggerInterface $logger = null
    ) {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('OpenRouter API key is required');
        }
    }

    // Public methods will be added in next steps
}
```

#### Step 4.2: Implement Validation Methods
Add private methods to `OpenRouterService.php`:
```php
private function validateMessages(array $messages): void
{
    if (empty($messages)) {
        throw new \InvalidArgumentException('Messages array cannot be empty');
    }

    $validRoles = ['system', 'user', 'assistant', 'developer', 'tool'];

    foreach ($messages as $index => $message) {
        if (!is_array($message)) {
            throw new \InvalidArgumentException("Message at index $index must be an array");
        }

        if (!isset($message['role'])) {
            throw new \InvalidArgumentException("Message at index $index missing 'role' key");
        }

        if (!in_array($message['role'], $validRoles, true)) {
            throw new \InvalidArgumentException(
                "Invalid role '{$message['role']}' at index $index. " .
                "Must be one of: " . implode(', ', $validRoles)
            );
        }

        if (!isset($message['content']) || !is_string($message['content']) || trim($message['content']) === '') {
            throw new \InvalidArgumentException("Message at index $index missing or empty 'content'");
        }
    }
}

private function validateResponseFormat(?array $responseFormat): void
{
    if ($responseFormat === null) {
        return;
    }

    if (!isset($responseFormat['type']) || $responseFormat['type'] !== 'json_schema') {
        throw new \InvalidArgumentException("response_format type must be 'json_schema'");
    }

    if (!isset($responseFormat['json_schema'])) {
        throw new \InvalidArgumentException("response_format must contain 'json_schema' key");
    }

    $jsonSchema = $responseFormat['json_schema'];

    if (!isset($jsonSchema['name']) || !is_string($jsonSchema['name']) || trim($jsonSchema['name']) === '') {
        throw new \InvalidArgumentException("json_schema must have non-empty 'name'");
    }

    if (!isset($jsonSchema['strict']) || $jsonSchema['strict'] !== true) {
        throw new \InvalidArgumentException("json_schema must have 'strict' set to true");
    }

    if (!isset($jsonSchema['schema']) || !is_array($jsonSchema['schema'])) {
        throw new \InvalidArgumentException("json_schema must contain 'schema' array");
    }

    $schema = $jsonSchema['schema'];

    if (!isset($schema['type']) || $schema['type'] !== 'object') {
        throw new \InvalidArgumentException("schema type must be 'object'");
    }

    if (!isset($schema['properties']) || !is_array($schema['properties'])) {
        throw new \InvalidArgumentException("schema must contain 'properties' array");
    }
}

private function validateParameters(array $parameters): void
{
    $validations = [
        'temperature' => fn($v) => is_numeric($v) && $v >= 0 && $v <= 2,
        'top_p' => fn($v) => is_numeric($v) && $v >= 0 && $v <= 1,
        'top_k' => fn($v) => is_int($v) && $v > 0,
        'max_tokens' => fn($v) => is_int($v) && $v > 0,
        'frequency_penalty' => fn($v) => is_numeric($v) && $v >= -2 && $v <= 2,
        'presence_penalty' => fn($v) => is_numeric($v) && $v >= -2 && $v <= 2,
        'seed' => fn($v) => is_int($v),
        'stream' => fn($v) => is_bool($v),
    ];

    foreach ($parameters as $key => $value) {
        if (isset($validations[$key]) && !$validations[$key]($value)) {
            throw new \InvalidArgumentException("Invalid value for parameter '$key'");
        }
    }
}
```

#### Step 4.3: Implement Request Building
Add to `OpenRouterService.php`:
```php
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
```

#### Step 4.4: Implement HTTP Request Logic
Add to `OpenRouterService.php`:
```php
private function sendRequest(string $endpoint, array $payload): array
{
    $url = $this->baseUrl . $endpoint;

    $this->logRequest($endpoint, $payload);

    try {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        $this->handleErrorResponse($response->status(), $response->json());
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        throw new \App\Exceptions\OpenRouter\OpenRouterNetworkException(
            'Network error: ' . $e->getMessage(),
            0,
            $e
        );
    }
}

private function handleErrorResponse(int $statusCode, array $error): never
{
    $message = $error['error']['message'] ?? 'Unknown error';

    match ($statusCode) {
        401 => throw new \App\Exceptions\OpenRouter\OpenRouterAuthenticationException(
            'Authentication failed: ' . $message,
            401
        ),
        429 => throw new \App\Exceptions\OpenRouter\OpenRouterRateLimitException(
            'Rate limit exceeded: ' . $message,
            429
        ),
        400 => throw str_contains($message, 'does not support')
            ? new \App\Exceptions\OpenRouter\OpenRouterModelNotSupportedException($message, 400)
            : new \App\Exceptions\OpenRouter\OpenRouterInvalidRequestException($message, 400),
        default => throw new \App\Exceptions\OpenRouter\OpenRouterApiException(
            'API error: ' . $message,
            $statusCode
        ),
    };
}
```

#### Step 4.5: Implement Response Parsing
Add to `OpenRouterService.php`:
```php
private function parseResponse(array $response, bool $expectJson = false): OpenRouterResponse
{
    $content = $response['choices'][0]['message']['content'] ?? '';

    if ($expectJson && is_string($content)) {
        $content = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \App\Exceptions\OpenRouter\OpenRouterApiException(
                'Failed to parse JSON response: ' . json_last_error_msg()
            );
        }
    }

    return new OpenRouterResponse(
        id: $response['id'] ?? '',
        model: $response['model'] ?? '',
        content: $content,
        usage: $response['usage'] ?? [],
        finishReason: $response['choices'][0]['finish_reason'] ?? '',
        raw: $response
    );
}
```

#### Step 4.6: Implement Logging
Add to `OpenRouterService.php`:
```php
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
        'id' => $response->id,
        'model' => $response->model,
        'finish_reason' => $response->finishReason,
        'tokens_used' => $response->getTokensUsed(),
    ]);
}
```

---

### Phase 5: Implement Public API Methods

#### Step 5.1: Main chat() Method
Add to `OpenRouterService.php`:
```php
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
        throw new \InvalidArgumentException('Model cannot be empty');
    }

    $payload = $this->buildRequestPayload($messages, $model, $responseFormat, $parameters);
    $response = $this->sendRequest('/chat/completions', $payload);
    $result = $this->parseResponse($response, $responseFormat !== null);

    $this->logResponse($result);

    return $result;
}
```

#### Step 5.2: Convenience Methods
Add to `OpenRouterService.php`:
```php
public function chatSimple(
    string $userMessage,
    ?string $systemMessage = null,
    string $model = 'openai/gpt-3.5-turbo'
): string {
    $messages = [];

    if ($systemMessage !== null) {
        $messages[] = ['role' => 'system', 'content' => $systemMessage];
    }

    $messages[] = ['role' => 'user', 'content' => $userMessage];

    $response = $this->chat($messages, $model);

    return $response->content;
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

    if (!is_array($response->content)) {
        throw new \App\Exceptions\OpenRouter\OpenRouterApiException(
            'Expected structured response but got string'
        );
    }

    return $response->content;
}

public function chatStream(
    array $messages,
    string $model,
    callable $callback,
    array $parameters = []
): void {
    $parameters['stream'] = true;

    $this->validateMessages($messages);
    $this->validateParameters($parameters);

    $payload = $this->buildRequestPayload($messages, $model, null, $parameters);
    $url = $this->baseUrl . '/chat/completions';

    $this->logRequest('/chat/completions', $payload);

    Http::timeout($this->timeout)
        ->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])
        ->withOptions(['stream' => true])
        ->post($url, $payload)
        ->onBody(function ($chunk) use ($callback) {
            $lines = explode("\n", $chunk);
            foreach ($lines as $line) {
                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);
                    if ($data === '[DONE]') {
                        return;
                    }
                    $json = json_decode($data, true);
                    if (isset($json['choices'][0]['delta']['content'])) {
                        $callback($json['choices'][0]['delta']['content']);
                    }
                }
            }
        });
}
```

#### Step 5.3: Configuration Methods
Add to `OpenRouterService.php`:
```php
public function setDefaultModel(string $model): self
{
    $this->defaultModel = $model;
    return $this;
}

public function setDefaultTemperature(float $temperature): self
{
    if ($temperature < 0 || $temperature > 2) {
        throw new \InvalidArgumentException('Temperature must be between 0 and 2');
    }
    $this->defaultTemperature = $temperature;
    return $this;
}

public function setTimeout(int $seconds): self
{
    if ($seconds <= 0) {
        throw new \InvalidArgumentException('Timeout must be positive');
    }
    $this->timeout = $seconds;
    return $this;
}
```

---

### Phase 6: Service Provider Registration

#### Step 6.1: Register Service in AppServiceProvider
Edit `app/Providers/AppServiceProvider.php`:
```php
use App\Services\OpenRouter\OpenRouterService;
use Psr\Log\LoggerInterface;

public function register(): void
{
    $this->app->singleton(OpenRouterService::class, function ($app) {
        return new OpenRouterService(
            apiKey: config('services.openrouter.api_key'),
            baseUrl: config('services.openrouter.base_url'),
            logger: $app->make(LoggerInterface::class)
        );
    });
}
```

---

#### Step 9.2: Add Request Retry with Exponential Backoff
Implement the `sendRequestWithRetry()` method from section 5.3.
---

### Phase 10: Deployment Checklist

- [ ] Ensure `OPENROUTER_API_KEY` is set in production `.env`
- [ ] Verify all exception handlers are in place
- [ ] Confirm logging is configured correctly (no sensitive data logged)
- [ ] Test rate limiting behavior
- [ ] Validate timeout settings for production workloads
- [ ] Review security considerations (section 6)
- [ ] Run full test suite
- [ ] Document API usage in team wiki/docs
- [ ] Set up monitoring/alerts for API errors
- [ ] Configure queue workers if using async processing

