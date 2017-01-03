<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Context\Spin\TimeoutException;
use Pim\Behat\Context\PimContext;

class AttributeTabContext extends PimContext
{
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
     * @param string $locale
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
        $this->getCurrentPage()->compareFieldValue($fieldName, $expected, true);
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
        try {
            $this->getCurrentPage()
                ->getElement('Attribute tab')
                ->getComparisonFieldContainer($fieldLabel);
        } catch (TimeoutException $e) {
            return true;
        }

        throw $this->getMainContext()->createExpectationException(
            sprintf(
                'Expected to not see the field "%s".',
                $fieldLabel
            )
        );
    }
}
