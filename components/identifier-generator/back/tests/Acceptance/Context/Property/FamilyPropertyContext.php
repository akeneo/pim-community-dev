<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Property;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyPropertyContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When /^I try to create an identifier generator with family property without required field$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithFamilyPropertyWithoutRequiredField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'family']]);
    }

    /**
     * @When /^I try to create an identifier generator with invalid family property$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithInvalidFamilyProperty(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'family', 'process' => ['type' => 'no'], 'unknown' => '']]);
    }

    /**
     * @When /^I try to create an identifier generator with empty family process property$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithEmptyFamilyProcessProperty(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'family', 'process' => []]]);
    }

    /**
     * @When /^I try to create an identifier generator with a family containing invalid truncate process$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithFamilyContainingInvalidTruncateProcess(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'family', 'process' => ['type' => 'truncate', 'operator' => '=', 'value' => '1', 'unknown' => '']]]);
    }

    /**
     * @When /^I try to create an identifier generator with a family process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithFamilyProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = [
            'type' => 'family',
            'process' => ['type' => $type, 'operator' => $operator, 'value' => $value],
        ];
        if ($operator === 'undefined') {
            unset($defaultStructure['process']['operator']);
        }
        if ($value === 'undefined') {
            unset($defaultStructure['process']['value']);
        }
        $this->tryToCreateGenerator(structure: [$defaultStructure]);
    }

    /**
     * @When I try to update an identifier generator with family property without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFamilyPropertyWithoutRequiredField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family']]);
    }

    /**
     * @When /^I try to update an identifier generator with invalid family property$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithInvalidFamilyProperty(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family', 'process' => ['type' => 'no'], 'unknown' => '']]);
    }

    /**
     * @When I try to update an identifier generator with empty family process property
     */
    public function iTryToUpdateAnIdentifierGeneratorWithEmptyFamilyProcessProperty(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family', 'process' => []]]);
    }

    /**
     * @When I try to update an identifier generator with a family containing invalid truncate process
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAFamilyContainingInvalidTruncateProcess(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family', 'process' => ['type' => 'truncate', 'operator' => '=', 'value' => '1', 'unknown' => '']]]);
    }

    /**
     * @When /^I try to update an identifier generator with a family process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFamilyProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = ['type' => 'family', 'process' => ['type' => $type, 'operator' => $operator, 'value' => $value]];
        if ($operator === 'undefined') {
            unset($defaultStructure['process']['operator']);
        }
        if ($value === 'undefined') {
            unset($defaultStructure['process']['value']);
        }
        $this->tryToUpdateGenerator(structure: [$defaultStructure]);
    }
}
