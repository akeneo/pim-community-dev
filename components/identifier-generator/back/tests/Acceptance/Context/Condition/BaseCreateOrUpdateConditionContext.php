<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Condition;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BaseCreateOrUpdateConditionContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When /^I try to create an identifier generator \
     *     with an? (?P<type>simple_select|multi_select|family|enabled|category) condition\
     *     (?:(?: with| and|,) (?P<attributeCode>[^ ]*) attribute)?\
     *     (?:(?: with| and|,) (?P<operator>[^ ]*) operator)?\
     *     (?:(?: with| and|,) (?P<scope>[^ ]*) scope)?\
     *     (?:(?: with| and|,) (?P<locale>[^ ]*) locale)?\
     *     (?:(?: with| and|,) (?P<value>.*) as value)?\
     *     (?P<unknown>(?: with| and|,) an unknown property)?$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithCondition(
        string $type,
        string $attributeCode = '',
        string $operator = '',
        string $scope = '',
        string $locale = '',
        string $value = '',
        string $unknown = '',
    ): void {
        $defaultCondition = $this->getCondition($type, $attributeCode, $scope, $locale, $value, $operator, $unknown);
        $this->tryToCreateGenerator(conditions: [$defaultCondition]);
    }

    /**
     * @When /^I try to update an identifier generator \
     *     with an? (?P<type>simple_select|multi_select|family|enabled|category) condition\
     *     (?:(?: with| and|,) (?P<attributeCode>[^ ]*) attribute)?\
     *     (?:(?: with| and|,) (?P<operator>[^ ]*) operator)?\
     *     (?:(?: with| and|,) (?P<scope>[^ ]*) scope)?\
     *     (?:(?: with| and|,) (?P<locale>[^ ]*) locale)?\
     *     (?:(?: with| and|,) (?P<value>.*) as value)?\
     *     (?P<unknown>(?: with| and|,) an unknown property)?$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithCondition(
        string $type,
        string $attributeCode = '',
        string $operator = '',
        string $scope = '',
        string $locale = '',
        string $value = '',
        string $unknown = '',
    ): void {
        $defaultCondition = $this->getCondition($type, $attributeCode, $scope, $locale, $value, $operator, $unknown);
        $this->tryToUpdateGenerator(conditions: [$defaultCondition]);
    }

    private function getCondition(
        string $type,
        string $attributeCode,
        string $scope,
        string $locale,
        string $value,
        string $operator,
        string $unknown
    ): array {
        $defaultCondition = $this->getValidCondition($type);
        if ($attributeCode !== '') {
            $defaultCondition['attributeCode'] = $attributeCode;
        }
        if ('undefined' === $scope) {
            unset($defaultCondition['scope']);
        } elseif ('' !== $scope) {
            $defaultCondition['scope'] = $scope;
        }
        if ('undefined' === $locale) {
            unset($defaultCondition['locale']);
        } elseif ('' !== $locale) {
            $defaultCondition['locale'] = $locale;
        }
        if ('undefined' === $value) {
            unset($defaultCondition['value']);
        } elseif ($value !== '') {
            $defaultCondition['value'] = \json_decode($value);
        }
        if ('undefined' === $operator) {
            unset($defaultCondition['operator']);
        } elseif ($operator !== '') {
            $defaultCondition['operator'] = $operator;
        }
        if ($unknown !== '') {
            $defaultCondition['unknown'] = 'unknown property';
        }

        return $defaultCondition;
    }
}
