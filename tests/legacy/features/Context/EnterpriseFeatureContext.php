<?php

namespace Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Traits\ClosestTrait;
use Pim\Behat\Context\AttributeValidationContext;
use Pim\Behat\Context\Domain\Collect\ImportProfilesContext;
use Pim\Behat\Context\Domain\Enrich\AttributeTabContext;
use Pim\Behat\Context\Domain\Enrich\CompletenessContext;
use Pim\Behat\Context\Domain\Enrich\FamilyVariantConfigurationContext;
use Pim\Behat\Context\Domain\Enrich\ProductGroupContext;
use Pim\Behat\Context\Domain\SecondaryActionsContext;
use Pim\Behat\Context\Domain\Spread\ExportBuilderContext;
use Pim\Behat\Context\Domain\Spread\ExportProfilesContext;
use Pim\Behat\Context\Domain\Spread\XlsxFileContext;
use Pim\Behat\Context\Domain\System\PermissionsContext;
use Pim\Behat\Context\Domain\TreeContext;
use Pim\Behat\Context\Storage\FileInfoStorage;
use Pim\Behat\Context\Storage\ProductStorage;
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
    use ClosestTrait;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->contexts['fixtures'] = $environment->getContext(EnterpriseFixturesContext::class);
        $this->contexts['catalogConfiguration'] = $environment->getContext(CatalogConfigurationContext::class);
        $this->contexts['domain-family-variants'] = $environment->getContext(FamilyVariantConfigurationContext::class);
        $this->contexts['webUser'] = $environment->getContext(EnterpriseWebUser::class);
        $this->contexts['datagrid'] = $environment->getContext(EnterpriseDataGridContext::class);
        $this->contexts['command'] = $environment->getContext(EnterpriseCommandContext::class);
        $this->contexts['navigation'] = $environment->getContext(NavigationContext::class);
        $this->contexts['assertions'] = $environment->getContext(EnterpriseAssertionContext::class);
        $this->contexts['domain-attribute-tab'] = $environment->getContext(AttributeTabContext::class);
        $this->contexts['domain-completeness'] = $environment->getContext(CompletenessContext::class);
        $this->contexts['domain-export-profiles'] = $environment->getContext(ExportProfilesContext::class);
        $this->contexts['domain-xlsx-files'] = $environment->getContext(XlsxFileContext::class);
        $this->contexts['domain-import-profiles'] = $environment->getContext(ImportProfilesContext::class);
        $this->contexts['domain-tree'] = $environment->getContext(TreeContext::class);
        $this->contexts['domain-secondary-actions'] = $environment->getContext(SecondaryActionsContext::class);
        $this->contexts['domain-group'] = $environment->getContext(ProductGroupContext::class);
        $this->contexts['file_transformer'] = $environment->getContext(EnterpriseFileTransformerContext::class);
        $this->contexts['hook'] = $environment->getContext(HookContext::class);
        $this->contexts['job'] = $environment->getContext(JobContext::class);
        $this->contexts['viewSelector'] = $environment->getContext(ViewSelectorContext::class);
        $this->contexts['amWidget'] = $environment->getContext(WidgetContext::class);
        $this->contexts['amProject'] = $environment->getContext(ProjectContext::class);
        $this->contexts['dashboard'] = $environment->getContext(DashboardContext::class);
        $this->contexts['storage-product'] = $environment->getContext(ProductStorage::class);
        $this->contexts['storage-file-info'] = $environment->getContext(FileInfoStorage::class);
        $this->contexts['attribute-validation'] = $environment->getContext(AttributeValidationContext::class);
        $this->contexts['role'] = $environment->getContext(PermissionsContext::class);
        $this->contexts['export-builder'] = $environment->getContext(ExportBuilderContext::class);
        $this->contexts['security'] =  $environment->getContext(EnterpriseSecurityContext::class);
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
     * @throws ExpectationException
     *
     * @Then /^I should see that (.*) is a smart attribute$/
     */
    public function iShouldSeeThatAttributeIsASmartAttribute(string $attribute): void
    {
        $element = $this->getSubcontext('navigation')->getCurrentPage()->findField($attribute);
        if (!$element) {
            throw $this->createExpectationException(sprintf('Expecting to see attribute "%s".', $attribute));
        }

        $smartElement = $this->spin(function () use ($element) {
            $fieldContainer = $this->getClosest($element, 'AknFieldContainer');
            return $fieldContainer->find('css', '.from-smart');
        }, sprintf('No smart attribute found for %s', $attribute));

        $expected = sprintf('This attribute can be updated by a rule');
        if ($smartElement->getText() !== $expected) {
            throw $this->createExpectationException(sprintf(
                'Smart attribute text does not match: found "%s", expected "%s"',
                $smartElement->getText(),
                $expected
            ));
        }
    }

    /**
     * @param string $attribute
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should not see that (.*) is a smart attribute$/
     */
    public function iShouldNotSeeThatAttributeIsASmartAttribute($attribute)
    {
        return $this->spin(function () use ($attribute) {
            $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($attribute);

            foreach ($icons as $icon) {
                if ($icon->getParent()->hasClass('from-smart')) {
                    throw $this->createExpectationException('"Affected by a rule icon" was found');
                }
            }

            return true;
        }, sprintf('Cannot find the smart attribute "%s"', $attribute));
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

        $expectedUserGroups = $this->listToArray($userGroups);
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
        $this->spin(function () use ($search) {
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

        $this->spin(function () use ($version) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.entity-version[data-version="%s"] .restore', $version));
        }, sprintf('Cannot find product version "%s"', $version))->click();

        $this->getSubcontext('navigation')->getCurrentPage()->confirmDialog();

        $this->spin(function () use ($total) {
            return $total === count($this->getSession()->getPage()->findAll('css', '.entity-version[data-version]'));
        }, 'Revert failed');
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
