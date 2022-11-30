<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductIdentifier
{
    public function __construct(
        private string $identifier
    ) {
    }

    /**
     * Returns the prefixes and the associated number
     * For example, an identifier AKN-123-FOO will return these prefixes: [
     *   'AKN-' => 123,
     *   'AKN-1' => 23,
     *   'AKN-12' => 3,
     * ]
     *
     * @return array<string, int>
     */
    public function getPrefixes(): array
    {
        $results = [];
        for ($i = 0; $i < strlen($this->identifier); $i++) {
            $charAtI = substr($this->identifier, $i, 1);
            if (is_numeric($charAtI)) {
                $prefix = substr($this->identifier, 0, $i);
                $beginningNumbers = $this->getAllBeginningNumbers(substr($this->identifier, $i));
                if ($beginningNumbers < PHP_INT_MAX) {
                    $results[$prefix] = $beginningNumbers;
                }
            }
        }

        return $results;
    }

    /**
     * Returns all the beginning numbers from a string
     * Ex: "251-toto" will return 251
     */
    private function getAllBeginningNumbers(string $identifierFromAnInteger): int
    {
        $matches = [];
        \preg_match('/^(?P<number>\d+)/', $identifierFromAnInteger, $matches);

        return \intval($matches['number']);
    }
}
