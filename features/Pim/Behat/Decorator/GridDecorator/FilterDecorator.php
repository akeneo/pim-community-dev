<?php

namespace Pim\Behat\Decorator\GridDecorator;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manipulate activated filters
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    const FILTER_CONTAINS         = 1;
    const FILTER_DOES_NOT_CONTAIN = 2;
    const FILTER_IS_EQUAL_TO      = 3;
    const FILTER_STARTS_WITH      = 4;
    const FILTER_ENDS_WITH        = 5;
    const FILTER_IS_EMPTY         = 'empty';
    const FILTER_IN_LIST          = 'in';

    protected $selectors = [
        'Filters'                  => '.filter-box',
        'Filters list'             => 'div.filter-list', // 'div' is mandatory here
        'Grid toolbar'             => 'div.grid-toolbar',
        'Filter list autocomplete' => '#select2-drop .select2-results',
    ];

    /**
     * Show a filter from the management list
     *
     * @param string $filterName
     */
    public function showFilter($filterName)
    {
        $toBeShown = false;

        try {
            if (!$this->getFilter($filterName)->isVisible()) {
                $toBeShown = true;
            }
        } catch (TimeoutException $e) {
            $toBeShown = true;
        }

        if ($toBeShown) {
            $this->searchInFilterList($filterName);
            $filter = $this->getFilterFromFilterList($filterName)->find('css', 'input');
            $filter->check();
        }
    }

    /**
     * Hide a filter from the management list
     *
     * @param string $filterName
     */
    public function hideFilter($filterName)
    {
        $toHide = false;
        try {
            if ($this->getFilter($filterName)->isVisible()) {
                $toHide = true;
            }

        } catch (TimeoutException $e) {
            $toHide = false;
        }

        if ($toHide) {
            $this->searchInFilterList($filterName);
            $filter = $this->getFilterFromFilterList($filterName)->find('css', 'input');
            $filter->uncheck();
        }
    }

    /**
     * @param string $filterName
     *
     * @return bool
     */
    public function isFilterAvailable($filterName)
    {
        $this->showFilterList();

        $filterElement = $this
            ->getFilterList()
            ->find('css', sprintf('label:contains("%s")', $filterName));

        return null !== $filterElement;
    }

    /**
     * Click on a filter in filter management list
     *
     * @param string $filterName
     *
     * @return NodeElement
     */
    public function getFilterFromFilterList($filterName)
    {
        $this->showFilterList();
        return $this->spin(function () use ($filterName) {
            return $this->getFilterList()
                ->find('css', sprintf('label:contains("%s")', $filterName));
        }, sprintf('Impossible to find filter "%s"', $filterName));
    }

    /**
     * Search for a filter in the list of filters
     *
     * @param $filterName
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function searchInFilterList($filterName)
    {
        $this->showFilterList();
        $manageFilters = $this->getFilterList();
        $searchField = $this->spin(function () use ($manageFilters) {
            return $manageFilters->find('css', 'input[type="search"]');
        }, 'Cannot find manage filters search field');
        $searchField->setValue($filterName);
    }

    /**
     * Open the filter
     *
     * @param NodeElement $filter
     */
    public function openFilter(NodeElement $filter)
    {
        $element = $this->spin(function () use ($filter) {
            return $filter->find('css', 'button');
        }, 'Impossible to open filter or maybe its type is not yet implemented');

        $element->click();
    }

    /**
     * Get grid filter from label name
     *
     * @param string $filterName
     *
     * @return NodeElement
     */
    public function getFilter($filterName)
    {
        $filter = $this->spin(function () use ($filterName) {
            if (strtolower($filterName) === 'channel') {
                $filter = $this->find('css', $this->selectors['Grid toolbar'])->find('css', 'div.filter-item');
            } else {
                $filter = $this->find('css', sprintf('div.filter-item:contains("%s")', $filterName));
            }

            return $filter;
        }, sprintf('Couldn\'t find a filter with name "%s"', $filterName));

        return $filter;
    }

    /**
     * @param string               $filterName The name of the filter
     * @param string               $value      The value to filter by
     * @param bool|string          $operator   If false, no operator will be selected
     * @param DriverInterface|null $driver     Required to filter by multiple choices
     */
    public function filterBy($filterName, $value, $operator = false, DriverInterface $driver = null)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        if ($elt = $filter->find('css', 'select')) {
            if ($elt->getText() === "between not between more than less than is empty") {
                $this->filterByDate($filter, $value, $operator);
            } elseif ($elt->getParent()->find('css', 'button.ui-multiselect')) {
                if (!$driver || !$driver instanceof Selenium2Driver) {
                    throw new \InvalidArgumentException('Selenium2Driver is required to filter by a choice filter');
                }
                $values = explode(',', $value);

                foreach ($values as $value) {
                    $driver->executeScript(
                        sprintf(
                            "$('.ui-multiselect-menu:visible input[title=\"%s\"]').click().trigger('click');",
                            $value
                        )
                    );
                    sleep(1);
                }

                // Uncheck the 'All' option
                if (!in_array('All', $values)) {
                    $driver->executeScript(
                        "var all = $('.ui-multiselect-menu:visible input[title=\"All\"]');" .
                        "if (all.length && all.is(':checked')) { all.click().trigger('click'); }"
                    );
                }
            }
        } elseif ($elt = $filter->find('css', 'div.filter-criteria')) {
            $results = $this->getFilterAutocomplete();
            $select2 = $filter->find('css', '.select2-input');

            if (false !== $operator) {
                $filter->find('css', 'button.dropdown-toggle')->click();
                $filter->find('css', sprintf('[data-value="%s"]', $operator))->click();
            }

            if (null !== $results && null !== $select2) {
                if (in_array($value, ['empty', 'is empty'])) {
                    // Allow passing 'empty' as value too (for backwards compability with existing scenarios)
                    $filter->find('css', 'button.dropdown-toggle')->click();
                    $filterValue = $filter->find('css', '[data-value="empty"]');
                    // In reference data, the first click hides the autocompletion of the filter
                    // but does not show the filter type list
                    if (!$filterValue->isVisible()) {
                        $filter->find('css', 'button.dropdown-toggle')->click();
                    }
                    $filterValue->click();
                } else {
                    $values = explode(',', $value);
                    foreach ($values as $value) {
                        $driver->getWebDriverSession()
                            ->element('xpath', $select2->getXpath())
                            ->postValue(['value' => [$value]]);
                        sleep(2);
                        $results->find('css', 'li')->click();
                        sleep(2);
                    }
                }
            } elseif ($value !== false) {
                $elt->fillField('value', $value);
            }

            $filter->find('css', 'button.filter-update')->click();
        } else {
            throw new \InvalidArgumentException(
                sprintf('Filtering by "%s" is not yet implemented"', $filterName)
            );
        }
    }

    /**
     * Make sure a filter is visible
     *
     * @param string $filterName
     */
    public function assertFilterVisible($filterName)
    {
        if (!$this->getFilter($filterName)->isVisible()) {
            throw new \InvalidArgumentException(
                sprintf('Filter "%s" is not visible', $filterName)
            );
        }
    }

    /**
     * Make sure a filter is visible
     *
     * @param string $filterName
     */
    public function assertFilterNotVisible($filterName)
    {
        try {
            $isVisible = $this->getFilter($filterName)->isVisible();
        } catch (TimeoutException $e) {
            $isVisible = false;
        }

        if (true === $isVisible) {
            throw new \InvalidArgumentException(
                sprintf('Filter "%s" is not visible', $filterName)
            );
        }
    }

    /**
     * @param string $filterName The name of the price filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param number $value      Value to filter
     * @param string $currency   Currency on which to filter
     */
    public function filterPerPrice($filterName, $action, $value, $currency)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        if (null !== $value) {
            $criteriaElt = $filter->find('css', 'div.filter-criteria');
            $criteriaElt->fillField('value', $value);
        }

        $buttons        = $filter->findAll('css', '.currencyfilter button.dropdown-toggle');
        $actionButton   = array_shift($buttons);
        $currencyButton = array_shift($buttons);

        // Open the dropdown menu with currency list and click on $currency line
        $currencyButton->click();
        $currencyButton->getParent()->find('css', sprintf('ul a:contains("%s")', $currency))->click();

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $filterName The name of the metric filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param float  $value      Value to filter
     * @param string $unit       Unit on which to filter
     */
    public function filterPerMetric($filterName, $action, $value, $unit)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->fillField('value', $value);

        $buttons      = $filter->findAll('css', '.metricfilter button.dropdown-toggle');
        $actionButton = array_shift($buttons);
        $unitButton   = array_shift($buttons);

        // Open the dropdown menu with unit list and click on $unit line
        $unitButton->click();
        $unitButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $unit))->click();

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $filterName The name of the number filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param float  $value      Value to filter
     */
    public function filterPerNumber($filterName, $action, $value)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->fillField('value', $value);

        $buttons      = $filter->findAll('css', '.filter-criteria button.dropdown-toggle');
        $actionButton = array_shift($buttons);

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }


    /**
     * Get the list of filters to activate/deactivate
     *
     * @return \Pim\Behat\Decorator\ElementDecorator
     */
    protected function getFilterList()
    {
        return $this
            ->find('xpath', 'ancestor::body')
            ->find('css', $this->selectors['Filters list']);
    }

    /**
     * Show the list of filter by clicking on 'manage filters'
     */
    protected function showFilterList()
    {
        $manageFilters = $this->getFilterList();
        if (!$manageFilters->isVisible()) {
            $filterList = $this->spin(function () {
                return $this->find('css', '#add-filter-button');
            }, 'Impossible to find filter list');

            $filterList->click();
        }
    }

    /**
     * Return the values proposed for the opened filter
     *
     * @return \Pim\Behat\Decorator\ElementDecorator
     */
    protected function getFilterAutocomplete()
    {
        return $this
            ->find('xpath', 'ancestor::body')
            ->find('css', $this->selectors['Filter list autocomplete']);
    }

    /**
     * @param NodeElement $filter
     * @param string      $value
     * @param string      $operator
     */
    protected function filterByDate($filter, $value, $operator)
    {
        $elt = $filter->find('css', 'select');
        if ('empty' === $operator) {
            $elt->selectOption('is empty');
        } else {
            $elt->selectOption($operator);
        }

        $filter->find('css', 'button.filter-update')->click();
    }
}
