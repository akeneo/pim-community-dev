<?php

namespace Pim\Behat\Context\Domain\Spread;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

class ExportBuilderContext extends PimContext
{
    use SpinCapableTrait;
    
    /**
     * Set the operator and the value of a filter
     *
     * Example:
     * When I filter by "Updated time condition" with operator "Updated products since the defined date" with value "05/25/2016"
     *
     * @param string $filter
     * @param string $expectedOperator
     * @param string $exceptedValue
     * 
     * @When /^I filter by "([^"]*)" with operator "([^"]*)" with value "([^"]*)"$/
     */
    public function iFilterBy($filter, $expectedOperator, $exceptedValue)
    {
        $filterName = $this->formatElementName($filter);
        $filterElement = $this->getCurrentPage()->getElement($filterName);

        $filterElement->setValue($expectedOperator, $exceptedValue);
    }

    /**
     * Check the value and the operator of the filter
     *
     * Example:
     * Then the filter "Updated time condition" should contain operator "Updated products since the last n days" with value "10"
     *
     * @param string $filter
     * @param string $expectedOperator
     * @param string $exceptedValue
     * 
     * @throws \Exception
     * 
     * @Then /^the filter "([^"]*)" should contain operator "([^"]*)" with value "([^"]*)"$/
     */
    public function theFilterShouldContains($filter, $expectedOperator, $exceptedValue)
    {
        $filterName = $this->formatElementName($filter);
        $filterElement = $this->getCurrentPage()->getElement($filterName);

        $filterElement->validate($expectedOperator, $exceptedValue);
    }

    /**
     * Check if the element is visible
     * 
     * Example:
     * Then I should not see the exported time date
     *
     * @param string $field
     * 
     * @throws \Exception
     * 
     * @Then /^I should not see the "([^"]*)" element in the filter "([^"]*)"$/
     */
    public function iShouldNotSeeTheDateInput($field, $filter)
    {
        $input = $this->getCurrentPage()->getElement($this->formatElementName($filter));
        $input->hasVisibleFilterValue($this->formatElementName($field));
    }

    /**
     * Format the name of an element from guerkin syntax
     * 
     * @param string $name
     *
     * @return string
     */
    private function formatElementName($name)
    {
        return str_replace(' ', '_', strtolower($name));
    }
}
