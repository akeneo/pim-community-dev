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

    public function count()
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

    /**
     * Merges an existing violation list into this list.
     */
    public function addAll(self $otherList): void
    {
        $this->errors = array_merge($otherList, $this->errors);
    }

    /**
     * Converts the violation into a string for debugging purposes.
     *
     * @return string
     */
    public function __toString(): string
    {
        $string = '';

        foreach ($this->errors as $error) {
            $string .= $error->getMessage()."\n";
        }

        return $string;
    }
}
