<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Property;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberPropertyContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When I try to create an identifier generator with autoNumber without required field
     */
    public function iTryToCreateAnIdentifierGeneratorWithAutonumberWithoutRequiredField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'auto_number', 'numberMin' => 4]]);
    }

    /**
     * @When /^I try to create an identifier generator with an auto number with '(?P<numberMin>[^']*)' as number min and '(?P<digitsMin>[^']*)' as min digits$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnAutoNumberWithNumberMinAndDigitsMin(int $numberMin, int $digitsMin): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'auto_number', 'numberMin' => $numberMin, 'digitsMin' => $digitsMin]]);
    }

    /**
     * @When I try to create an identifier generator with multiple auto number in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithMultipleAutoNumberInStructure(): void
    {
        $this->tryToCreateGenerator(structure: [
            ['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3],
            ['type' => 'auto_number', 'numberMin' => 1, 'digitsMin' => 4],
        ]);
    }

    /**
     * @When I try to update an identifier generator with autoNumber without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberWithoutRequiredField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'numberMin' => 4]]);
    }

    /**
     * @When /^I try to update an identifier generator with an auto number with '(?P<numberMin>[^']*)' as number min and '(?P<digitsMin>[^']*)' as min digits$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAnAutoNumberWithNumberMinAndDigitsMin(int $numberMin, int $digitsMin): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'numberMin' => $numberMin, 'digitsMin' => $digitsMin]]);
    }
}
