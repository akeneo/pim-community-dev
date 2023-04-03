<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Property;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityPropertyContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When /^I try to create an identifier generator with a reference entity property without attribute code$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithAReferenceEntityPropertyWithoutAttributeCode(): void
    {
        $this->tryToCreateGenerator(structure: [
            ['type' => 'reference_entity', 'process' => ['type' => 'no']],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with reference entity property without process field$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithReferenceEntityPropertyWithoutProcessField(): void
    {
        $this->tryToCreateGenerator(structure: [
            ['type' => 'reference_entity', 'attributeCode' => 'color'],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with a reference_entity property with (?P<attributeCode>[^']*) attribute(?: and (?P<scope>.*) scope)?(?: and (?P<locale>.*) locale)?$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithAReferenceEntityPropertyWithNameAttribute(
        string $attributeCode,
        string $scope = '',
        string $locale = ''
    ): void {
        $refEntityProperty = ['type' => 'reference_entity', 'attributeCode' => $attributeCode, 'process' => ['type' => 'no']];

        if ($scope) {
            $refEntityProperty['scope'] = $scope;
        }

        if ($locale) {
            $refEntityProperty['locale'] = $locale;
        }

        $this->tryToCreateGenerator(structure: [
            $refEntityProperty,
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with a reference entity process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithReferenceEntityProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = [
            'attributeCode' => 'brand',
            'type' => 'reference_entity',
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
     * @When I try to update an identifier generator with a reference entity property without attribute code
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAReferenceEntityPropertyWithoutAttributeCode(): void
    {
        $this->tryToUpdateGenerator(structure: [
            ['type' => 'reference_entity', 'process' => ['type' => 'no']],
        ]);
    }

    /**
     * @When I try to update an identifier generator with reference entity property without process field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithReferenceEntityPropertyWithoutProcessField(): void
    {
        $this->tryToUpdateGenerator(structure: [
            ['type' => 'reference_entity', 'attributeCode' => 'color'],
        ]);
    }

    /**
     * @When /^I try to update an identifier generator with a reference_entity property with (?P<attributeCode>[^']*) attribute(?: and (?P<scope>.*) scope)?(?: and (?P<locale>.*) locale)?$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAReferenceEntityPropertyWithNameAttribute(
        string $attributeCode,
        string $scope = '',
        string $locale = ''
    ): void {
        $refEntityProperty = ['type' => 'reference_entity', 'attributeCode' => $attributeCode, 'process' => ['type' => 'no']];

        if ($scope) {
            $refEntityProperty['scope'] = $scope;
        }

        if ($locale) {
            $refEntityProperty['locale'] = $locale;
        }

        $this->tryToUpdateGenerator(structure: [
            $refEntityProperty,
        ]);
    }

    /**
     * @When /^I try to update an identifier generator with a reference entity process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithReferenceEntityProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = [
            'attributeCode' => 'brand',
            'type' => 'reference_entity',
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
