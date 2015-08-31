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
     * | product_label | author | "Lace color" base | "Lace color" changed | status               |
     * | my-hoody      | mary   |                   | Black                | Waiting for approval |
     * or
     * | product label | author | "Lace color" insert | status               |
     * | my-hoody      | mary   | Black               | Waiting for approval |
     *
     * There are three types of changes displayed in proposals :
     *     "base" is the replaced data
     *     "changed" is the new data
     *     "insert" is an inserted data (there was no attribute before for example)
     *
     * @Given /^I should see the following proposals:$/
     *
     * @param TableNode $table
     */
    public function iShouldSeeTheFollowingProposals(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $expectedGenericValues = [];
            $expectedChanges = [];
            foreach ($row as $key => $value) {
                switch (strtolower($key)) {
                    case 'product_label':
                    case 'product label':
                        $expectedGenericValues['product label'] = $value;
                        break;
                    case 'author':
                        $expectedGenericValues['author'] = $value;
                        break;
                    case 'status':
                        $expectedGenericValues['status'] = $value;
                        break;
                    default:
                        $expectedChanges[] = $this->getExpectedChanges($key, $value);
                        break;
                }
            }

            $proposalRow     = $this->getProposalByProductLabel($expectedGenericValues['product label']);
            $actualAttrParts = $this->getAttributePartFromProposalRow($proposalRow);

            $this->assertProposalGenericValues($expectedGenericValues);
            $this->assertProposalChanges($actualAttrParts, $expectedChanges);
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
                    'scope'  => null
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
        }, 5);

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
     * @param array $expectedGenericValues All values that are not in changes and indexed by their column name
     *
     * @throws ExpectationException
     */
    protected function assertProposalGenericValues(array $expectedGenericValues)
    {
        $page = $this->getSubcontext('navigation')->getCurrentPage();
        foreach ($expectedGenericValues as $column => $value) {
            $actualValue = $page->getColumnValue($column, $expectedGenericValues['product label']);
            if ($actualValue !== $value) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see %s "%s" but got "%s".', $column, $value, $actualValue)
                );
            }
        }
    }

    /**
     * @param array $actualAttrParts see getActualAttributePart()
     * @param array $expectedChanges see getExpectingChanges()
     *
     * @throws ExpectationException
     */
    protected function assertProposalChanges(array $actualAttrParts, array $expectedChanges)
    {
        foreach ($expectedChanges as $expectedAttrChange) {
            $actualAttrDiff = $actualAttrParts[$expectedAttrChange['attribute']];
            if (null === $actualAttrDiff) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s" in changes.', $expectedAttrChange['attribute']));
            }

            $actualDiff = $actualAttrDiff->findAll('css', sprintf('.diff .%s', $expectedAttrChange['type']));
            if (null === $actualDiff) {
                throw $this->createExpectationException(sprintf('Expecting to see "%s" type in changes.', $expectedAttrChange['type']));
            }

            foreach ($actualDiff as $diff) {
                $diffKey = array_search($diff->getText(), $expectedAttrChange['values']);
                if (false === $diffKey) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Changes "%s" was not expected for attribute "%s".',
                            $diff->getText(),
                            $expectedAttrChange['attribute']
                        )
                    );
                }
                unset($expectedAttrChange['values'][$diffKey]);
            }

            if (!empty($expectedAttrChange['values'])) {
                $missingValues = implode(', ', $expectedAttrChange['values']);
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting to see "%s" in %s for attribute "%s".',
                        $missingValues,
                        $expectedAttrChange['type'],
                        $expectedAttrChange['attribute']
                    )
                );
            }
        }
    }

    /**
     * Gets the attribute label and changes part from a proposal datagrid row
     *
     * @param NodeElement $proposalRow
     *
     * @throws ExpectationException
     *
     * @return NodeElement[]
     */
    protected function getAttributePartFromProposalRow(NodeElement $proposalRow)
    {
        $details        = $proposalRow->find('css', 'div.details');
        $attrLabel      = null;
        $attributesPart = [];
        foreach ($details->findAll('css', 'dd, dt') as $child) {
            if (null !== $attrLabel) {
                $attributesPart[$attrLabel] = $child;
                $attrLabel = null;
                continue;
            }
            if (null === $child->find('css', 'ul.diff')) {
                $attrLabel = $child->getText();
            }
        }

        return $attributesPart;
    }

    /**
     * Gets the proposal row from the proposals grid in terms of product label
     *
     * @param string $productLabel
     *
     * @throws ExpectationException
     *
     * @return NodeElement
     */
    protected function getProposalByProductLabel($productLabel)
    {
        try {
            $page = $this->getSubcontext('navigation')->getCurrentPage();
            $row  = $page->getRow($productLabel);
            if (null === $row) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $productLabel));
            }
        } catch (ElementNotFoundException $e) {
            throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $productLabel));
        }

        return $row;
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
