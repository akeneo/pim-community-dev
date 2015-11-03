<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

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
        if (isset($parameters['timeout']) && '' !== $parameters['timeout']) {
            $this->timeout = $parameters['timeout'];
        } else {
            $this->timeout = FeatureContext::DEFAULT_TIMEOUT;
        }

        $this->useContext('fixtures', new EnterpriseFixturesContext());
        $this->useContext('catalogConfiguration', new EnterpriseCatalogConfigurationContext());
        $this->useContext('webUser', new EnterpriseWebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new EnterpriseDataGridContext());
        $this->useContext('navigation', new EnterpriseNavigationContext($parameters['base_url']));
        $this->useContext('transformations', new EnterpriseTransformationContext());
        $this->useContext('assertions', new EnterpriseAssertionContext());
        $this->useContext('technical', new TechnicalContext());
        $this->useContext('command', new EnterpriseCommandContext());
        $this->useContext('asset', new EnterpriseAssetContext());
        $this->useContext('file_transformer', new EnterpriseFileTransformerContext());
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
     * @param string $label
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should see that (.*) is a modified value$/
     */
    public function iShouldSeeThatFieldIsAModifiedValue($label)
    {
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($label);
        foreach ($icons as $icon) {
            if ($icon->hasClass('modified-by-draft')) {
                return true;
            }
        }

        throw $this->createExpectationException(sprintf('Field "%s" should be marked as modified by draft', $label));
    }

    /**
     * Expects table as :
     * | product  | author | attribute  | original | new         |
     * | my-hoody | Mary   | Lace color |          | Black;White |
     *
     * Note: As values are not ordered you can add multiple values using semicolon separator.
     * Warning: we split the results with space separator so values with spaces will fail.
     *
     * @Given /^I should see the following proposals:$/
     *
     * @param TableNode $table
     */
    public function iShouldSeeTheFollowingProposals(TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $page = $this->getSubcontext('navigation')->getCurrentPage();

            // Assert the change is good
            $change = $this->spin(function () use ($page, $hash) {
                return $page->find('css', sprintf(
                    'table.proposal-changes[data-product="%s"][data-attribute="%s"][data-author="%s"]',
                    $hash['product'],
                    $hash['attribute'],
                    $hash['author']
                ));
            }, 20, sprintf('Unable to find the change on the proposal for attribute "%s"', $hash['attribute']));

            $original = $this->spin(function () use ($change) {
                return $change->find('css', '.original-value');
            }, 20, 'Unable to find the original value of the change');

            $new = $this->spin(function () use ($change) {
                return $change->find('css', '.new-value');
            }, 20, 'Unable to find the new value of the change');

            $originalExpectedValues = explode(';', $hash['original']);
            $newExpectedValues = explode(';', $hash['new']);
            $originalValues = explode(' ', $original->getText());
            $newValues = explode(' ', $new->getText());

            foreach ($originalExpectedValues as $originalExpectedValue) {
                assertContains($originalExpectedValue, $originalValues, sprintf(
                    '"%s" original value not found in "%s".',
                    $originalExpectedValue,
                    json_encode($originalValues)
                ));
            }

            foreach ($newExpectedValues as $newExpectedValue) {
                assertContains($newExpectedValue, $newValues, sprintf(
                    '"%s" original value not found in "%s".',
                    $newExpectedValue,
                    json_encode($newValues)
                ));
            }

            assertEquals(count($originalExpectedValues), count($originalValues));
            assertEquals(count($newExpectedValues), count($newValues));
        }
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
                    'scope'  => null
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
                    'scope'  => null
                ],
                $action
            );

            $actualAction = $actualActions[$key];

            $this->checkRuleElementValue(
                $actualAction->find('css', '.action-field'),
                $action['field'],
                true,
                true
            );
            $type = $actualAction->find('css', '.action-type.set-value');
            if (null === $type) {
                throw $this->createExpectationException('Expecting to see set-value field');
            }
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
                    'scope'  => null
                ],
                $action
            );

            $actualAction = $actualActions[$key];

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
            $type = $actualAction->find('css', '.action-type.copy-value');
            if (null === $type) {
                throw $this->createExpectationException('Expecting to see copy-value field');
            }
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
        $info = $this->spin(function () {
            return $this->getSession()->getPage()->find('css', '.meta .draft-status');
        });

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
        }, 30, sprintf('The popover does not contain %s', $search));
    }

    /**
     * @When /^I revert the product version number (\d+)$/
     */
    public function iRevertTheProductVersionNumber($version)
    {
        $button = $this->spin(function () use ($version) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('tr[data-version="%s"]', $version))
                ->find('css', 'td.actions .btn.restore');
        });

        $button->click();
        $this->getSubcontext('navigation')->getCurrentPage()->confirmDialog();

        $this->wait();
    }

    /**
     * @Given /^I start to manage assets for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function iStartToManageAssetsFor($field)
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

    /**
     * Gets expecting proposal changes from header and value
     * Return [
     *     'attribute' => (string) attribute label,
     *     'type'      => (string should be 'base'|'changed'|'insert') changes type,
     *     'values'    => (array) each value contained in $rawValues and separated by ;,
     * ]
     *
     * @param string $header
     * @param string $rawValues
     *
     * @return array
     */
    protected function getExpectedChanges($header, $rawValues)
    {
        $expectedAttributeLabel = [];
        preg_match('/^"([a-zA-Z0-9_ ]+)"/', $header, $expectedAttributeLabel);

        $expectedChangesType = trim(strrchr($header, ' '));
        $expectedValues      = explode(';', $rawValues);
        $expectedValues      = array_map('trim', $expectedValues);

        return [
            'attribute' => $expectedAttributeLabel[1],
            'type'      => $expectedChangesType,
            'values'    => $expectedValues,
        ];
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
