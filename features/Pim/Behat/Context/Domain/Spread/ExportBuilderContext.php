<?php

namespace Pim\Behat\Context\Domain\Spread;

use Behat\Mink\Exception\ExpectationException;
use Context\Page\Base\Grid;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory as PageObjectFactory;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAware;

class ExportBuilderContext extends PimContext implements PageObjectAware
{
    use SpinCapableTrait;

    /** @var PageObjectFactory */
    private $pageFactory;

    /**
     * {@inheritdoc}
     */
    public function setPageObjectFactory(PageObjectFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
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

    /**
     * @Given /^I switch the locale from "([^"]*)" filter to "([^"]*)"$/
     */
    public function iSwitchTheLocaleFromFilterTo($filter, $locale)
    {
        $filter = $this->getDatagrid()->getFilter($filter);
        $filter->setLocale($locale);
    }

    /**
     * @Given /^I switch the scope from "([^"]*)" filter to "([^"]*)"$/
     */
    public function iSwitchTheScopeFromFilterTo($filter, $scope)
    {
        $filter = $this->getDatagrid()->getFilter($filter);
        $filter->setScope($scope);
    }
    
    /**
     * @return Grid
     */
    protected function getDatagrid()
    {
        return $this->createPage('Base\Grid');
    }
}
