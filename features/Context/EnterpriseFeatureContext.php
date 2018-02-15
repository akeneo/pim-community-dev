<?php

namespace Context;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use PimEnterprise\Behat\Context\DashboardContext;
use PimEnterprise\Behat\Context\HookContext;
use PimEnterprise\Behat\Context\JobContext;
use PimEnterprise\Behat\Context\NavigationContext;
use PimEnterprise\Behat\Context\TeamworkAssistant\ProjectContext;
use PimEnterprise\Behat\Context\TeamworkAssistant\WidgetContext;
use PimEnterprise\Behat\Context\ViewSelectorContext;

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
        parent::__construct($parameters);

        $this->useContext('fixtures', new EnterpriseFixturesContext());
        $this->useContext('catalogConfiguration', new EnterpriseCatalogConfigurationContext());
        $this->useContext('webUser', new EnterpriseWebUser());
        $this->useContext('datagrid', new EnterpriseDataGridContext());
        $this->useContext('navigation', new EnterpriseNavigationContext($parameters['base_url']));
        $this->useContext('security', new EnterpriseSecurityContext($parameters['base_url']));
        $this->useContext('transformations', new EnterpriseTransformationContext());
        $this->useContext('assertions', new EnterpriseAssertionContext());
        $this->useContext('command', new EnterpriseCommandContext());
        $this->useContext('asset', new EnterpriseAssetContext());
        $this->useContext('file_transformer', new EnterpriseFileTransformerContext());
        $this->useContext('hook', new HookContext($parameters['window_width'], $parameters['window_height']));
        $this->useContext('job', new JobContext());
        $this->useContext('navigation', new NavigationContext($parameters['base_url']));
        $this->useContext('viewSelector', new ViewSelectorContext());
        $this->useContext('amWidget', new WidgetContext());
        $this->useContext('amProject', new ProjectContext());

        $this->useContext('dashboard', new DashboardContext());
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
        $this->spin(function () use ($label) {
            $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($label);

            foreach ($icons as $icon) {
                if ($icon->hasClass('modified-by-draft')) {
                    return true;
                }
            }
        }, sprintf('Field "%s" should be marked as modified by draft', $label));
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
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($attribute);

        foreach ($icons as $icon) {
            if ($icon->getParent()->hasClass('from-smart')) {
                return true;
            }
        }

        throw $this->createExpectationException('Affected by a rule icon was not found');
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
            return $this->getSession()->getPage()->find('css', '.AknTitleContainer-meta .draft-status');
        }, 'Cannot find draft status');

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
        $this->getMainContext()->spin(function () use ($search) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.popover .popover-content:contains("%s")', $search));
        }, sprintf('The popover does not contain "%s"', $search));
    }

    /**
     * @When /^I revert the product version number (\d+)$/
     * @When /^I revert the product version number (\d+) and then see (\d+) total versions$/
     */
    public function iRevertTheProductVersionNumber($version, $total = null)
    {
        $version = (int) $version;
        $total = (null === $total ? $version + 1 : (int) $total);

        $button = $this->spin(function () use ($version) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('tr[data-version="%s"]', $version))
                ->find('css', 'td.actions .restore');
        }, sprintf('Cannot find product version "%s"', $version));

        $button->click();

        $this->getSubcontext('navigation')->getCurrentPage()->confirmDialog();

        $this->spin(function () use ($total) {
            return $total === count($this->getSession()->getPage()->findAll('css', 'tr[data-version]'));
        }, 'Revert failed');
    }

    /**
     * @Given /^I start to manage assets for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function iStartToManageAssetsFor($field)
    {
        $assetCollectionPicker = $this->spin(function () use ($field) {
            return $this->getSubcontext('navigation')->getCurrentPage()->findFieldContainer($field)->getParent();
        }, 'Did not find the asset collection picker');

        $manageAssets = $this->spin(function () use ($assetCollectionPicker) {
            return $assetCollectionPicker->find('css', '.add-asset');
        }, 'Did not find the manage asset button');
        $manageAssets->click();

        $this->spin(function () {
            return $this->getSession()->getPage()
                ->find('css', '#grid-asset-picker-grid[data-rendered="true"]');
        }, 'Cannot find asset picker grid');
    }

    /**
     * @Given /^I remove "([^"]*)" from the asset basket$/
     */
    public function iRemoveFromTheAssetBasket($entity)
    {
        $removeButton = $this->spin(function () use ($entity) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.asset-basket li[data-asset="%s"] .remove-asset', $entity));
        }, 'Cannot find button to remove asset in basket');

        $removeButton->click();
    }

    /**
     * @param string $page
     *
     * @When /^I set the current page to "([^"]*)"$/
     */
    public function iSetTheCurrentPageTo($page)
    {
        $this->getMainContext()->getSubcontext('navigation')->setCurrentPage($page);
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
