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
        return count($this->errors);
    }

    public function add(Error $error): void
    {
        $this->errors[] = $error;
    }

    public function getMergedMessages(): string
    {
        return \join("\n", array_map(fn ($error) => $error->getMessage(), $this->errors));
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
