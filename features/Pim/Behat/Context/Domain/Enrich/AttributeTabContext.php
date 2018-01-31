<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class AttributeTabContext extends PimContext
{
    use ClosestTrait;

    use SpinCapableTrait;

    /**
     * @When /^I open the comparison panel$/
     */
    public function iStartTheCopy()
    {
        $this->getElementOnCurrentPage('Attribute tab')
            ->startComparison();
    }

    /**
     * @param string $field
     *
     * @Given /^I select translations for "([^"]*)"$/
     */
    public function iSelectTranslationsFor($field)
    {
        $attributeTabElement = $this->getElementOnCurrentPage('Attribute tab');

        $attributeTabElement->manualSelectComparedElement($field);
    }

    /**
     * @param string $mode
     *
     * @Given /^I select (.*) translations$/
     */
    public function iSelectTranslations($mode)
    {
        $this->getElementOnCurrentPage('Comparison panel')
            ->selectElements($mode);
    }

    /**
     * @Given /^I copy selected translations$/
     */
    public function iCopySelectedTranslations()
    {
        $this->getElementOnCurrentPage('Comparison panel')
            ->copySelectedElements();
    }

    /**
     * @param string $type
     * @param string $selection
     *
     * @When /^I switch the comparison (locale|scope|source) to "([^"]*)"$/
     */
    public function iSwitchTheComparisonContextTo($type, $selection)
    {
        $method = 'switch' . ucfirst($type);

        $this->getElementOnCurrentPage('Comparison panel')
            ->$method($selection);
    }

    /**
     * @param string $fieldName
     * @param string $expected
     *
     * @Then /^the ([^"]*) comparison value should be "([^"]*)"$/
     */
    public function theComparisonValueShouldBe($fieldName, $expected)
    {
        $this->spin(function () use ($fieldName, $expected) {
            $this->getCurrentPage()->compareFieldValue($fieldName, $expected, true);

            return true;
        }, sprintf('Cannot compare product value for "%s" field', $fieldName));
    }

    /**
     * @param string $fieldNames
     * @param mixed  $not
     *
     * @throws TimeoutException
     *
     * @Then /^the ([^"]*) fields? should (not )?be highlighted$/
     */
    public function theFieldShouldBeHighlighted($fieldNames, $not = null)
    {
        $fields = preg_split('/, */', $fieldNames);

        $this->spin(function () use ($fields, $not) {
            foreach ($fields as $field) {
                $fieldNode = $this->getCurrentPage()->findField($field);
                if (null === $fieldNode) {
                    return false;
                }

                $badge = $this
                    ->getClosest($fieldNode, 'field-container')
                    ->find('css', '.AknBadge--highlight:not(.AknBadge--hidden)');

                if ((null === $not && null === $badge) || (null !== $not && null !== $badge)) {
                    return false;
                }
            }

            return true;
        }, sprintf('Expected to see the groups "%s" %shighlited', $fieldNames, $not));
    }

    /**
     * @param string      $groupNames
     * @param string|null $not
     *
     * @throws TimeoutException
     *
     * @Then /^the ([^"]*) groups? should (not )?be highlighted$/
     */
    public function theGroupShouldBeHighlighted($groupNames, $not = null)
    {
        $groups = preg_split('/, */', $groupNames);

        $this->spin(function () use ($groups, $not) {
            foreach ($groups as $group) {
                $groupNode = $this->getCurrentPage()->getGroup($group);
                if (null === $groupNode) {
                    return false;
                }

                $badge = $groupNode->find('css', '.AknBadge--highlight:not(.AknBadge--hidden)');
                if ((null === $not && null === $badge) || (null !== $not && null !== $badge)) {
                    return false;
                }
            }

            return true;
        }, sprintf('Expected to see the groups "%s" %shighlited', $groupNames, $not));
    }

    /**
     * @Then /^I should see the comparison field "([^"]*)"$/
     */
    public function iShouldSeeTheComparisonField($fieldLabel)
    {
        $attributeTab = $this->getElementOnCurrentPage('Attribute tab');

        $field = $this->spin(function () use ($attributeTab, $fieldLabel) {
            return $attributeTab->getLabelField($fieldLabel);
        }, sprintf('Cannot find the %s on comparison field', $fieldLabel));

        Assert::assertNotNull($field);
    }

    /**
     * @Then /^I should not see the comparison field "([^"]*)"$/
     */
    public function iShouldNotSeeTheComparisonField($fieldLabel)
    {
        $this->spin(function () use ($fieldLabel) {
            try {
                $this->getElementOnCurrentPage('Attribute tab')->getLabelField($fieldLabel);
            } catch (TimeoutException $e) {
                return true;
            }
        }, sprintf(
            'Expected to not see the field "%s".',
            $fieldLabel
        ));
    }
}
