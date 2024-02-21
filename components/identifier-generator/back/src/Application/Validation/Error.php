<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Error
{
    /**
     * @param string $message
     * @param string[] $parameters
     * @param string|null $path
     */
    public function __construct(
        private readonly string $message,
        private readonly array $parameters = [],
        private readonly ?string $path = null
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return array{path: string | null, message: string}
     */
    public function normalize(): array
    {
        return [
            'path' => $this->path,
            'message' => $this->message,
        ];
    }

    public function __toString(): string
    {
        if (null !== $this->path && '' !== $this->path) {
            return \sprintf("%s: %s", $this->path, $this->message);
        }

        return $this->message;
    }
}
