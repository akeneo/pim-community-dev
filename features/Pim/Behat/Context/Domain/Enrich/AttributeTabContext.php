<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Context\PimContext;

class AttributeTabContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @When /^I open the comparison panel$/
     */
    public function iStartTheCopy()
    {
        $this->getCurrentPage()
            ->getElement('Attribute tab')
            ->startComparison();
    }

    /**
     * @param string $field
     *
     * @Given /^I select translations for "([^"]*)"$/
     */
    public function iSelectTranslationsFor($field)
    {
        $this->getCurrentPage()
            ->getElement('Attribute tab')
            ->manualSelectComparedElement($field);
    }

    /**
     * @param string $mode
     *
     * @Given /^I select (.*) translations$/
     */
    public function iSelectTranslations($mode)
    {
        $this->getCurrentPage()
            ->getElement('Comparison panel')
            ->selectElements($mode);
    }

    /**
     * @Given /^I copy selected translations$/
     */
    public function iCopySelectedTranslations()
    {
        $this->getCurrentPage()
            ->getElement('Comparison panel')
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

        $this->getCurrentPage()
            ->getElement('Comparison panel')
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
     * @param string $fieldName
     * @param mixed  $not
     *
     * @throws TimeoutException
     *
     * @Then /^the ([^"]*) field should (not )?be highlighted$/
     */
    public function theFieldShouldBeHighlighted($fieldName, $not = null)
    {
        $field = $this->getCurrentPage()->findField($fieldName);
        try {
            $this->spin(function () use ($field) {
                return $field->getParent()->getParent()->find('css', '.AknBadge--highlight:not(.AknBadge--hidden)');
            }, 'Cannot find the badge element');
        } catch (TimeoutException $e) {
            if ('not ' !== $not) {
                throw $e;
            } else {
                return;
            }
        }

        if ('not ' === $not) {
            throw $this->getMainContext()->createExpectationException(
                sprintf(
                    'Expected to not see the field "%s" not highlighted.',
                    $fieldName
                )
            );
        }
    }

    /**
     * @param string $groupName
     * @param mixed $not
     *
     * @throws TimeoutException
     *
     * @Then /^the ([^"]*) group should (not )?be highlighted$/
     */
    public function theGroupShouldBeHighlighted($groupName, $not = null)
    {
        $group = $this->getCurrentPage()->getGroup($groupName);
        try {
            $this->spin(function () use ($group) {
                return $group->find('css', '.AknBadge--highlight:not(.AknBadge--hidden)');
            }, 'Cannot find the badge element');
        } catch (TimeoutException $e) {
            if ('not ' !== $not) {
                throw $e;
            } else {
                return;
            }
        }

        if ('not ' === $not) {
            throw $this->getMainContext()->createExpectationException(
                sprintf(
                    'Expected to see the group "%s" not highlighted.',
                    $groupName
                )
            );
        }
    }

    /**
     * @Then /^I should see the comparison field "([^"]*)"$/
     */
    public function iShouldSeeTheComparisonField($fieldLabel)
    {
        $field = $this->getCurrentPage()
            ->getElement('Attribute tab')
            ->getComparisonFieldContainer($fieldLabel);

        assertNotNull($field);
    }

    /**
     * @Then /^I should not see the comparison field "([^"]*)"$/
     */
    public function iShouldNotSeeTheComparisonField($fieldLabel)
    {
        $this->spin(function () use ($fieldLabel) {
            try {
                $this->getCurrentPage()
                    ->getElement('Attribute tab')
                    ->getComparisonFieldContainer($fieldLabel);
            } catch (TimeoutException $e) {
                return true;
            }
        }, sprintf(
            'Expected to not see the field "%s".',
            $fieldLabel
        ));
    }
}
