<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ErrorList implements \Countable
{
    /**
     * @param Error[] $errors
     */
    public function __construct(private array $errors = [])
    {
    }

    public function count(): int
    {
        return \count($this->errors);
    }

    /**
     * @return array<array{message: string, path: string | null}>
     */
    public function normalize(): array
    {
        return \array_map(fn (Error $error): array => $error->normalize(), $this->errors);
    }

    public function __toString(): string
    {
        return \implode("\n", \array_map(fn (Error $error): string => $error->__toString(), $this->errors));
    }

    /**
     * @return Error[]
     */
    public function toArray(): array
    {
        return $this->errors;
    }
}
