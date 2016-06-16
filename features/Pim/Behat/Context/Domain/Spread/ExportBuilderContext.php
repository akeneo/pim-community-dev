<?php

namespace Pim\Behat\Context\Domain\Spread;

use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Pim\Behat\Decorator\Export\Filter\UpdatedTimeConditionDecorator;

class ExportBuilderContext extends PimContext
{
    use SpinCapableTrait;
    
    /**
     * Set the operator and the value of a filter
     *
     * Example:
     * When I filter exported products by operator "Updated products since the defined date" with value "05/25/2016"
     *
     * @param string $expectedOperator
     * @param string $exceptedValue
     * 
     * @When /^I filter exported products by operator "([^"]*)" and value "([^"]*)"$/
     */
    public function iFilterBy($expectedOperator, $exceptedValue)
    {
        $filterElement = $this->getCurrentPage()->getElement('Updated time condition');

        $filterElement->setValue($expectedOperator, $exceptedValue);
    }

    /**
     * Check the value and the operator of the filter
     *
     * Example:
     * Then the filter should contain operator "Updated products since the last n days" with value "10"
     *
     * @param string $expectedOperator
     * @param string $exceptedValue
     * 
     * @throws ExpectationException
     * 
     * @Then /^the filter should contain operator "([^"]*)" and value "([^"]*)"$/
     */
    public function theFilterShouldContains($expectedOperator, $exceptedValue)
    {
        /** @var UpdatedTimeConditionDecorator $filter */
        $filterElement = $this->getCurrentPage()->getElement('Updated time condition');
        $value = $filterElement->getValue();
        $operator = $filterElement->getOperator();

        if ($expectedOperator !== $operator) {
            throw new ExpectationException(
                sprintf(
                    'The value of operator does not contain "%s" but "%s"',
                    $expectedOperator,
                    $operator
                ),
                $this->getSession()->getDriver()
            );
        }

        if ($exceptedValue !== $value) {
            throw new ExpectationException(
                sprintf('The value of filter does not contain "%s" but "%s"', $exceptedValue, $value),
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * Check if the element is visible
     * 
     * Example:
     * Then I should not see the "updated since n days" element in the filter "Updated time condition"
     *
     * @param string $field
     * @param string $filterElement
     * 
     * @throws \Exception
     * 
     * @Then /^I should not see the "([^"]*)" element in the filter "([^"]*)"$/
     */
    public function iShouldNotSeeTheElement($field, $filterElement)
    {
        /** @var UpdatedTimeConditionDecorator $filterElement */
        $filterElement = $this->getCurrentPage()->getElement($filterElement);
        
        if ($filterElement->checkValueElementVisibility($field)) {
            throw new ExpectationException(
                sprintf('The element "%s" should not be visible', $field),
                $this->getSession()->getDriver()
            );
        }
    }
}
