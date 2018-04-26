<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\PimContext;

class VariantNavigationContext extends PimContext
{
    /**
     * @Then /^the variant navigation selected axis values for level (\d+) should be "([^"]*)"$/
     */
    public function theVariantNavigationSelectedAxisValuesForLevelShouldBe(int $level, string $axisValues): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $element = $variantNavigation->getSelectedAxisValuesForLevel($level);
        $axisValues = strtolower($axisValues);
        $elementAxisValues = strtolower($element->getText());

        if ($axisValues !== $elementAxisValues) {
            throw $this->createExpectationException(sprintf(
                'Selected axis values for level "%s" should be "%s", but "%s" found.',
                $level,
                $axisValues,
                $elementAxisValues
            ));
        }
    }

    /**
     * @Given /^the variant navigation axis name for level (\d+) should be "([^"]*)"$/
     */
    public function theVariantNavigationAxisNameForLevelShouldBe(int $level, string $axisName): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $element = $variantNavigation->getAxisNameForLevel($level);
        $axisName = strtolower($axisName);
        $elementAxisName = str_replace(':', '', strtolower($element->getText()));

        if ($axisName !== $elementAxisName) {
            throw $this->createExpectationException(sprintf(
                'Axis name for level "%s" should be "%s", but "%s" found.',
                $level,
                $axisName,
                $elementAxisName
            ));
        }
    }

    /**
     * @When /^I open the variant navigation children selector for level (\d+)$/
     */
    public function iOpenTheVariantNavigationChildrenSelectorForLevel(int $level): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $selector = $variantNavigation->getChildrenSelectorForLevel($level);
        $selector->open();
    }

    /**
     * @Then /^I should( not)? see the "([^"]*)" element in the variant children selector for level (\d+)$/
     */
    public function iShouldSeeTheElementInTheVariantChildrenSelectorForLevel($not, string $label, int $level): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $selector = $variantNavigation->getChildrenSelectorForLevel($level);
        $availableValues = $selector->getAvailableValues();
        $availableLabels = [];

        // We just want the label, not the completeness numbers
        foreach ($availableValues as $availableValue) {
            $matches = [];
            preg_match('#^(.*) ((\d+ \/ \d+)?(\d+\%)?)$#', $availableValue, $matches);
            $availableLabels[] = $matches[1];
        }

        if (!in_array($label, $availableLabels) && !$not) {
            throw $this->createExpectationException(sprintf(
                'Could not find element "%s" in the variant children selector for level "%s"',
                $label,
                $level
            ));
        }

        if (in_array($label, $availableLabels) && $not) {
            throw $this->createExpectationException(sprintf(
                'Element "%s" in the variant children selector for level "%s" should not be visible',
                $label,
                $level
            ));
        }
    }

    /**
     * @When /^I filter the variant navigation children selector for level (\d+) with text "([^"]*)"$/
     */
    public function iFilterTheVariantNavigationChildrenSelectorForLevelWithText(int $level, string $text): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $selector = $variantNavigation->getChildrenSelectorForLevel($level);
        $selector->search($text);
    }

    /**
     * @When /^I select the child "([^"]*)" for level (\d+)$/
     */
    public function iSelectTheChildForLevel(string $label, int $level): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $selector = $variantNavigation->getChildrenSelectorForLevel($level);
        $selector->setValue($label);
    }

    /**
     * @When /^I navigate to the selected element for level (\d+)$/
     */
    public function iNavigateToTheSelectedElementForLevel(int $level): void
    {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $element = $variantNavigation->getSelectedAxisValuesForLevel($level);
        $element->click();
    }

    /**
     * @Then /^completeness for element "([^"]*)" in the variant children selector for level (\d+) should be "([^"]*)"$/
     */
    public function completenessForElementInTheVariantChildrenSelectorForLevelShouldBe(
        string $label,
        int $level,
        string $completeness
    ): void {
        $variantNavigation = $this->getCurrentPage()->getVariantNavigation();

        $selector = $variantNavigation->getChildrenSelectorForLevel($level);
        $availableValues = $selector->getAvailableValues();

        foreach ($availableValues as $availableValue) {
            $matches = [];
            preg_match('#^(.*) ((\d+ \/ \d+)?(\d+\%)?)$#', $availableValue, $matches);
            $itemLabel = $matches[1];
            $itemCompleteness = $matches[2];

            if ($label === $itemLabel && $completeness !== $itemCompleteness) {
                throw $this->createExpectationException(sprintf(
                    'Item "%s" for level "%s" should have "%s" for completeness, "%s" found',
                    $label,
                    $level,
                    $completeness,
                    $itemCompleteness
                ));

                break;
            }
        }
    }

    /**
     * Create an expectation exception
     *
     * @param string $message
     *
     * @return ExpectationException
     */
    protected function createExpectationException(string $message): ExpectationException
    {
        return $this->getMainContext()->createExpectationException($message);
    }
}
