<?php

namespace Pim\Behat\Context\Domain\Spread;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAware;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory as PageObjectFactory;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class ExportBuilderContext extends PimContext implements PageObjectAware
{
    use SpinCapableTrait;

    /**
     * @var PageObjectFactory
     */
    protected $pageFactory;

    /**
     * @param PageObjectFactory $pageFactory
     */
    public function setPageObjectFactory(PageObjectFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
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

        $element = $this->getElementOnCurrentPage('Attribute selector');

        $element->selectAttributes($attributes);
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
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    protected function getDatagrid(): Page
    {
        return $this->pageFactory->createPage('Base\Grid');
    }
}
