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
        $this->useContext('navigation', new EnterpriseNavigationContext());
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new EnterpriseAssertionContext());
        $this->useContext('technical', new TechnicalContext());
        $this->useContext('command', new EnterpriseCommandContext());
        $this->useContext('asset', new EnterpriseAssetContext());
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
     * @throws ExpectationException
     *
     * @return bool
     *
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
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should see that (.*) is a smart$/
     */
    public function iShouldSeeThatAttributeIsASmart($attribute)
    {
        $this->wait();
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($attribute);

        foreach ($icons as $icon) {
            if ($icon->getParent()->hasClass('from-smart')) {
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
                    'Expecting %d rule conditions, actually saw %d',
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

            $this->checkRuleElementValue(
                $actualCondition->find('css', '.condition-field'),
                $condition['field'],
                true,
                true
            );
            $this->checkRuleElementValue(
                $actualCondition->find('css', '.condition-operator'),
                $condition['operator']
            );
            $this->checkRuleElementValue(
                $actualCondition->find('css', '.condition-value'),
                $condition['value'],
                false
            );
            $this->checkRuleElementValue(
                $actualCondition->find('css', '.rule-item-context .locale'),
                $condition['locale'],
                false
            );
            $this->checkRuleElementValue(
                $actualCondition->find('css', '.rule-item-context .scope'),
                $condition['scope'],
                false
            );
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should see the following rule setter actions:$/
     */
    public function iShouldSeeTheFollowingRuleSetterActions(TableNode $table)
    {
        $expectedActions = $table->getHash();
        $actualActions = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-action.set-value-action');

        $expectedCount = count($expectedActions);
        $actualCount   = count($actualActions);
        if ($expectedCount !== $actualCount) {
            throw new \Exception(
                sprintf(
                    'Expecting %d rule actions, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedActions as $key => $action) {
            $action = array_merge(
                [
                    'locale' => null,
                    'scope' => null
                ],
                $action
            );

            $actualAction = $actualActions[$key];

            $action['type'] = 'is set into';

            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-field'),
                $action['field'],
                true,
                true
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-type'),
                $action['type']
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-value'),
                $action['value'],
                true,
                false
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.rule-item-context .locale'),
                $action['locale'],
                false
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.rule-item-context .scope'),
                $action['scope'],
                false
            );
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should see the following rule copier actions:$/
     */
    public function iShouldSeeTheFollowingRuleCopierActions(TableNode $table)
    {
        $expectedActions = $table->getHash();
        $actualActions = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-action.copy-value-action');

        $expectedCount = count($expectedActions);
        $actualCount   = count($actualActions);
        if ($expectedCount !== $actualCount) {
            throw new \Exception(
                sprintf(
                    'Expecting %d rules actions, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedActions as $key => $action) {
            $action = array_merge(
                [
                    'locale' => null,
                    'scope' => null
                ],
                $action
            );

            $actualAction = $actualActions[$key];

            $action['type'] = 'is copied into';

            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-field.from-field'),
                $action['from_field'],
                true,
                true
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-field.to-field'),
                $action['to_field'],
                true,
                true
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-type'),
                $action['type']
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.from-field .rule-item-context .locale'),
                $action['from_locale'],
                false
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.to-field .rule-item-context .locale'),
                $action['to_locale'],
                false
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.from-field .rule-item-context .scope'),
                $action['from_scope'],
                false
            );
            $this->checkRuleElementValue(
                $actualAction->find('css', '.to-field .rule-item-context .scope'),
                $action['to_scope'],
                false
            );
        }
    }

    /**
     * @param string $code
     *
     * @Given /^I delete the rule "([^"]*)"$/
     */
    public function iDeleteTheRule($code)
    {
        $rules = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-row');

        foreach ($rules as $rule) {
            if (strtolower($rule->find('css', '.rule-code')->getText()) === $code) {
                $rule->find('css', '.delete-row')->click();

                $this->wait();

                return;
            }
        }

        throw new \Exception(sprintf('No rule found with code %s', $code));
    }

    protected function checkRuleElementValue($element, $expectedValue, $mandatory = true, $firstElement = false)
    {
        $element = is_array($element) ? reset($element) : $element;

        if ($mandatory || null !== $element && $expectedValue !== null) {
            if ($element) {
                $actualValue = $firstElement ? explode('<', trim($element->getHTML()))[0] : $element->getText();
            } else {
                $actualValue = '';
            }

            if ($actualValue !== $expectedValue) {
                throw new \Exception(
                    sprintf(
                        'Rule element is expected to be "%s", actually is "%s"',
                        $expectedValue,
                        $actualValue
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
        $runner = $this->getContainer()->get('akeneo_rule_engine.runner.chained');
        $runner->run($rule);
    }

    /**
     * @Then /^I should see the smart icon for the attribute "([^"]*)"$/
     */
    public function iShouldSeeTheSmartIconForTheAttribute($attributeLabel)
    {
        $this->getAttributeIcon('i.from-smart', $attributeLabel);
    }

    /**
     * @Given /^I display the tooltip for the "([^"]*)" rule icon/
     */
    public function iDisplayTheTooltipForTheAttribute($attributeLabel)
    {
        $icon = $this->getAttributeIcon('i.from-smart', $attributeLabel);

        $icon->mouseOver();
    }

    /**
     * @Given /^I display the tooltip for the "([^"]+)" attribute modified$/
     */
    public function iDisplayTheTooltipForTheModifiedAttribute($attributeLabel)
    {
        $icon = $this->getAttributeIcon('i.from-modified', $attributeLabel);

        $icon->mouseOver();
    }

    /**
     * @Then /^I should see "([^"]*)" in the popover$/
     */
    public function iShouldSeeInThePopover($search)
    {
        $popoverContent = $this->getMainContext()->spin(function () use ($search) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.popover .popover-content:contains("%s")', $search));
        });

        if (!$popoverContent) {
            throw $this->createExpectationException(sprintf('The popover does not contain %s', $search));
        }
    }

    /**
     * @When /^I revert the product version number (\d+)$/
     */
    public function iRevertTheProductVersionNumber($version)
    {
        $this->getSession()
            ->getPage()
            ->find('css', sprintf('tr[data-version="%s"]', $version))
            ->find('css', 'td.actions .btn.restore')->click();
        $this->wait();
        $this->getSubcontext('navigation')->getCurrentPage()->confirmDialog();

        $this->wait();
    }

    /**
     * @Given /^I start to manage assets for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function iStartToManageAssetsOnAttributeFrontView($field)
    {
        $manageAssets = $this->spin(function () use ($field) {
            return $this->getSubcontext('navigation')->getCurrentPage()->findFieldContainer($field)->getParent()
                ->find('css', '.add-asset');
        });

        $manageAssets->click();

        $this->spin(function () {
            return $this->getSession()->getPage()
                ->find('css', '#grid-asset-picker-grid[data-rendered="true"]');
        });
    }

    /**
     * @Given /^I remove "([^"]*)" from the asset basket$/
     */
    public function iRemoveFromTheAssetBasket($entity)
    {
        $removeButton = $this->spin(function () use ($entity) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.asset-basket li[data-asset="%s"] .remove-asset', $entity));
        });

        $removeButton->click();
    }

    protected function getAttributeIcon($iconSelector, $attributeLabel)
    {
        $icon = $this->getSession()->getPage()
            ->find('css', sprintf('.attribute-field label:contains("%s")', $attributeLabel))
            ->getParent()
            ->find('css', $iconSelector);

        if (!$icon) {
            throw $this->createExpectationException('From rule icon was not found');
        }

        return $icon;
    }
}
