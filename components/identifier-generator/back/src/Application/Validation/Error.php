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
    public function __construct(private string $message, private array $parameters = [], private ?string $path = null)
    {
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
}
