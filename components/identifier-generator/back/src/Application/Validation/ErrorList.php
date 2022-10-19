<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ErrorList implements \Countable
{
    public function __construct(private array $errors = [])
    {
    }

    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * Adds an error to this list.
     */
    public function add(Error $error): void
    {
        $this->errors[] = $error;
    }

    public function getAllMessages(): string
    {
        $string = '';

        foreach ($this->errors as $error) {
            $string .= $error->getMessage()."\n";
        }

        return $string;
    }
}
