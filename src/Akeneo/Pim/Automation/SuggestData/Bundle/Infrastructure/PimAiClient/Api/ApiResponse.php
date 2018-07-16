<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api;

use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    private $responseCode;
    
    private $content;

    public function __construct(int $responseCode, array $content = [])
    {
        $this->responseCode = $responseCode;
        $this->content = $content;
    }

    public function code(): int
    {
        return $this->responseCode;
    }

    public function isSuccess(): bool
    {
        return $this->code() === Response::HTTP_OK;
    }

    public function content(): array
    {
        return $this->content;
    }
}
