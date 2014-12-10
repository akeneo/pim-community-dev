<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;

/**
 * A context for creating entities
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFeatureContext extends FeatureContext
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->useContext('fixtures', new EnterpriseFixturesContext());
        $this->useContext('catalogConfiguration', new EnterpriseCatalogConfigurationContext());
        $this->useContext('webUser', new EnterpriseWebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new EnterpriseDataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new EnterpriseNavigationContext());
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new AssertionContext());
    }

    /**
     * @BeforeScenario
     */
    public function registerConfigurationDirectory()
    {
        $this
            ->getSubcontext('catalogConfiguration')
            ->addConfigurationDirectory(__DIR__.'/catalog');
    }

    /**
     * @param string $field
     *
     * @return bool
     * @throws ExpectationException
     * @Then /^I should see that (.*) is a modified value$/
     */
    public function iShouldSeeThatFieldIsAModifiedValue($field)
    {
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($field);
        foreach ($icons as $icon) {
            if ($icon->hasClass('icon-file-text-alt')) {
                return true;
            }
        }

        throw $this->createExpectationException('Modified value icon was not found');
    }

    /**
     * @param string $attribute
     *
     * @return bool
     * @throws ExpectationException
     * @Then /^I should see that (.*) is a smart$/
     */
    public function iShouldSeeThatAttributeIsASmart($attribute)
    {
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($attribute);
        foreach ($icons as $icon) {
            if ($icon->hasClass('icon-code-fork')) {
                return true;
            }
        }

        throw $this->createExpectationException('Affected by a rule icon was not found');
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should see the following rule conditions:$/
     */
    public function iShouldSeeTheFollowingRuleConditions(TableNode $table)
    {
        $expectedConditions = $table->getHash();
        $actualConditions = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-condition');

        $expectedCount = count($expectedConditions);
        $actualCount   = count($actualConditions);
        if ($expectedCount !== $actualCount) {
            throw new \Exception(
                sprintf(
                    'Expecting %d rules conditions, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedConditions as $key => $condition) {
            $condition = array_merge(
                [
                    'locale' => null,
                    'scope' => null
                ],
                $condition
            );

            $actualCondition = $actualConditions[$key];

            $this->checkElementValue($actualCondition->findAll('css', '.condition-field'), $condition['field']);
            $this->checkElementValue($actualCondition->findAll('css', '.condition-operator'), $condition['operator']);
            $this->checkElementValue($actualCondition->findAll('css', '.condition-value'), $condition['value'], false);
            $this->checkElementValue($actualCondition->findAll('css', '.rule-item-context .locale'), $condition['locale']);
            $this->checkElementValue($actualCondition->findAll('css', '.rule-item-context .locale'), $condition['scope']);

        //     $fields    = $actualCondition->findAll('css', '.condition-field');
        //     $operators = $actualCondition->findAll('css', '.condition-operator');
        //     $values    = $actualCondition->findAll('css', '.condition-value');
        //     $locales   = $actualCondition->findAll('css', '.rule-item-context .locale');
        //     $scopes    = $actualCondition->findAll('css', '.rule-item-context .scope');

        //     $field    = reset($fields);
        //     $operator = reset($operators);
        //     $value    = reset($values);
        //     $locale   = reset($locales);
        //     $scope    = reset($scopes);

        //     if ($field->getText() !== $condition['field']) {
        //         throw new \Exception(
        //             sprintf(
        //                 'Rule #%d field is expected to be "%s", actually is "%s"',
        //                 $key + 1,
        //                 $condition['field'],
        //                 $field->getText()
        //             )
        //         );
        //     }

        //     if ($operator->getText() !== $condition['operator']) {
        //         throw new \Exception(
        //             sprintf(
        //                 'Rule #%d operator is expected to be "%s", actually is "%s"',
        //                 $key + 1,
        //                 $condition['operator'],
        //                 $operator->getText()
        //             )
        //         );
        //     }

        //     if (
        //         $condition['value'] !== '' && $value === null ||
        //         false !== $value && $value->getText() !== $condition['value']
        //     ) {
        //         throw new \Exception(
        //             sprintf(
        //                 'Rule #%d value is expected to be "%s", actually is "%s"',
        //                 $key + 1,
        //                 $condition['value'],
        //                 $value->getText()
        //             )
        //         );
        //     }

        //     if (
        //         $condition['value'] !== '' && $value === null ||
        //         false !== $value && $value->getText() !== $condition['value']
        //     ) {
        //         throw new \Exception(
        //             sprintf(
        //                 'Rule #%d value is expected to be "%s", actually is "%s"',
        //                 $key + 1,
        //                 $condition['value'],
        //                 $value->getText()
        //             )
        //         );
        //     }

        //     if (
        //         false === $locale && $condition['locale'] !== null ||
        //         false !== $locale && $locale->getText() !== $condition['locale']
        //     ) {
        //         throw new \Exception(
        //             sprintf(
        //                 'Rule #%d locale is expected to be "%s", actually is "%s"',
        //                 $key + 1,
        //                 $condition['locale'],
        //                 $locale->getText()
        //             )
        //         );
        //     }

        //     if (
        //         false === $scope && $condition['scope'] !== null ||
        //         false !== $scope && $scope->getText() !== $condition['scope']
        //     ) {
        //         throw new \Exception(
        //             sprintf(
        //                 'Rule #%d scope is expected to be "%s", actually is "%s"',
        //                 $key + 1,
        //                 $condition['scope'],
        //                 $scope->getText()
        //             )
        //         );
        //     }
        }
    }

    protected function checkElementValue($element, $expectedValue, $mandatory = true)
    {
        $element = is_array($element) ? reset($element) : $element;

        if ($mandatory) {
            if ($element->getText() !== $expectedValue) {
                throw new \Exception(
                    sprintf(
                        'Rule element is expected to be "%s", actually is "%s"',
                        $expectedValue,
                        $element->getText()
                    )
                );
            }
        } else {
            if (
                false === $element && $expectedValue !== null ||
                false !== $element && $element->getText() !== $expectedValue
            ) {
                throw new \Exception(
                    sprintf(
                        'Rule element is expected to be "%s", actually is "%s"',
                        $expectedValue,
                        $element->getText()
                    )
                );
            }
        }
    }

    /**
     * @param string $field
     * @param string $userGroups
     *
     * @throws ExpectationException
     * @Then /^I should see the permission (.*) with user groups (.*)$/
     */
    public function iShouldSeeThePermissionFieldWithRoles($field, $userGroups)
    {
        try {
            $element = $this->getSubcontext('navigation')->getCurrentPage()->findField($field);
            if (!$element) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
            }
        } catch (ElementNotFoundException $e) {
            throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
        }

        $selectedOptions = $element->getParent()->getParent()->findAll('css', 'li.select2-search-choice div');
        $selectedRoles = [];
        foreach ($selectedOptions as $option) {
            $selectedRoles[] = $option->getHtml();
        }

        $expectedUserGroups = $this->getMainContext()->listToArray($userGroups);
        $missingUserGroups = array_diff($selectedRoles, $expectedUserGroups);
        $extraUserGroups = array_diff($expectedUserGroups, $selectedRoles);
        if (count($missingUserGroups) > 0 || count($extraUserGroups) > 0) {
            throw $this->createExpectationException(
                sprintf(
                    'For permission %s, user groups %s are expected, user groups granted are %s',
                    $field,
                    implode(', ', $expectedUserGroups),
                    implode(', ', $selectedRoles)
                )
            );
        }
    }

    /**
     * @param string $status
     *
     * @throws \LogicException
     * @Then /^its status should be "([^"]*)"$/
     */
    public function itsStatusShouldBe($status)
    {
        $info = $this->getSession()->getPage()->find('css', '.navbar-content li:contains("Status")');

        if (false === strpos($info->getText(), $status)) {
            throw new \LogicException(
                sprintf(
                    'Expecting product status "%s", actually is "%s"',
                    $status,
                    $info->getText()
                )
            );
        }
    }

    /**
     * @param string $code
     *
     * @throws \LogicException
     * @Given /^the product rule "([^"]*)" is executed$/
     */
    public function iExecuteTheProductRule($code)
    {
        $rule = $this->getSubcontext('fixtures')->getRule($code);
        $runner = $this->getContainer()->get('pimee_rule_engine.runner.chained');
        $updated = $runner->run($rule);
    }
}
