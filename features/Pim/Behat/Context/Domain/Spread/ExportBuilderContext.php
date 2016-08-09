<?php

namespace Pim\Behat\Context\Domain\Spread;

use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Pim\Behat\Decorator\Export\Filter\UpdatedTimeConditionDecorator;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;

class ExportBuilderContext extends PimContext implements PageObjectAwareInterface
{
    use SpinCapableTrait;

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->filters = $pageFactory->createPage('Base\Grid');
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

    /**
     * @param string $attributes
     * @param string $gridLabel
     *
     * @Given /^I select the following attributes to export (.*)$/
     * @Given /^I select no attribute to export$/
     */
    public function iSelectTheFollowingAttributes($attributes = '')
    {
        $attributes = $this->getMainContext()->listToArray($attributes);

        $this->getCurrentPage()->getElement('Attribute selector')->selectAttributes($attributes);
    }
}
