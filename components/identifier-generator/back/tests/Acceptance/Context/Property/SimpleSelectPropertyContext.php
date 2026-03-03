<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Property;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectPropertyContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When /^I try to create an identifier generator with a simple select property without attribute code$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithASimpleSelectPropertyWithoutAttributeCode(): void
    {
        $this->tryToCreateGenerator(structure: [
            ['type' => 'simple_select', 'process' => ['type' => 'no']],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with simple select property without process field$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithSimpleSelectPropertyWithoutProcessField(): void
    {
        $this->tryToCreateGenerator(structure: [
            ['type' => 'simple_select', 'attributeCode' => 'color'],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with a simple_select property with (?P<attributeCode>[^']*) attribute(?: and (?P<scope>.*) scope)?(?: and (?P<locale>.*) locale)?$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithASimpleSelectPropertyWithNameAttribute(
        string $attributeCode,
        string $scope = '',
        string $locale = ''
    ): void {
        $simpleSelectProperty = ['type' => 'simple_select', 'attributeCode' => $attributeCode, 'process' => ['type' => 'no']];

        if ($scope) {
            $simpleSelectProperty['scope'] = $scope;
        }

        if ($locale) {
            $simpleSelectProperty['locale'] = $locale;
        }

        $this->tryToCreateGenerator(structure: [
            $simpleSelectProperty,
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with a simple select process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithSimpleSelectProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = [
            'attributeCode' => 'color',
            'type' => 'simple_select',
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
     * @When I try to update an identifier generator with a simple select property without attribute code
     */
    public function iTryToUpdateAnIdentifierGeneratorWithASimpleSelectPropertyWithoutAttributeCode(): void
    {
        $this->tryToUpdateGenerator(structure: [
            ['type' => 'simple_select', 'process' => ['type' => 'no']],
        ]);
    }

    /**
     * @When I try to update an identifier generator with simple select property without process field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithSimpleSelectPropertyWithoutProcessField(): void
    {
        $this->tryToUpdateGenerator(structure: [
            ['type' => 'simple_select', 'attributeCode' => 'color'],
        ]);
    }

    /**
     * @When /^I try to update an identifier generator with a simple_select property with (?P<attributeCode>[^']*) attribute(?: and (?P<scope>.*) scope)?(?: and (?P<locale>.*) locale)?$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithASimpleSelectPropertyWithNameAttribute(
        string $attributeCode,
        string $scope = '',
        string $locale = ''
    ): void {
        $simpleSelectProperty = ['type' => 'simple_select', 'attributeCode' => $attributeCode, 'process' => ['type' => 'no']];

        if ($scope) {
            $simpleSelectProperty['scope'] = $scope;
        }

        if ($locale) {
            $simpleSelectProperty['locale'] = $locale;
        }

        $this->tryToUpdateGenerator(structure: [
            $simpleSelectProperty,
        ]);
    }

    /**
     * @When /^I try to update an identifier generator with a simple select process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithSimpleSelectProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = [
            'attributeCode' => 'color',
            'type' => 'simple_select',
            'process' => ['type' => $type, 'operator' => $operator, 'value' => $value],
        ];
        if ($operator === 'undefined') {
            unset($defaultStructure['process']['operator']);
        }
        if ($value === 'undefined') {
            unset($defaultStructure['process']['value']);
        }
        $this->tryToUpdateGenerator(structure: [$defaultStructure]);
    }
}
