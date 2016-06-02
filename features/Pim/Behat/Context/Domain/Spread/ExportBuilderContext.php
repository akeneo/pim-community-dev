<?php

namespace Pim\Behat\Context\Domain\Spread;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

class ExportBuilderContext extends PimContext
{
    use SpinCapableTrait;
    
    /**
     * @When /^I filter by "Updated time condition" with operator "([^"]*)" with value "([^"]*)"$/
     */
    public function iChangeExportedTimeStrategyTo($operator, $value)
    {
        $select = $this->getCurrentPage()->getElement('export_time_strategy');
        $select->setValue($operator);

        $date = $this->getCurrentPage()->getElement('export_time_date');
        $date->setValue($value);
    }

    /**
     * @Then /^I should not see the exported time date$/
     */
    public function iShouldNotSeeTheDateInput()
    {
        $input = $this->getCurrentPage()->getElement('export_time_date');
        
        if ($input->isVisible()) {
            throw new \Exception('The date input for the updated condition time should not be visible');
        }
    }

    /**
     * @Then /^the filter "Updated time condition" should contain operator "([^"]*)" with value "([^"]*)"$/
     */
    public function theDateInputShouldContains($operator, $exceptedValue)
    {
        $input = $this->getCurrentPage()->getElement('export_time_date');
        
        $value = $input->getValue();

        if ($exceptedValue !== $value) {
            throw new \Exception(
                sprintf('The exported time date does not contain "%s" but "%s"', $exceptedValue, $value)
            );
        }
    }
}
