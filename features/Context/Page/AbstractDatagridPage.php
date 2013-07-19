<?php

namespace Context\Page;

use Behat\Mink\Element\NodeElement;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Behat context page for datagrid
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractDatagridPage extends Page
{
    protected $elements = array(
        'grid'        => array('css' => 'table.grid'),
        'grid-filter' => array('css' => 'div.filter-box')
    );

    /**
     * Get a row from the grid containing the value asked
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return NodeElement
     */
    public function getGridRow($value)
    {
        $gridRow = $this->getElement('grid')
                        ->find('css', sprintf('tr:contains("%s")', $value));

        if (!$gridRow) {
            throw new \InvalidArgumentException(
                sprintf('Couldn\'t find a row for value "%s"', $value)
            );
        }

        return $gridRow;
    }

    /**
     * Filter the filter name by the value
     * @param string $filterName
     * @param string $value
     */
    public function filterBy($filterName, $value)
    {
        $filter = $this->getFilter($filterName);
        // open the filter
        $this->openFilter($filter);
        // set the value
        $filterCriteria = $filter->find('css', 'div.filter-criteria');
        $filterCriteria->fillField('value', $value);
        // update the grid
        $filterCriteria->find('css', 'button.filter-update')->click();
    }

    /**
     * Open the filter depending of its type
     * @param NodeElement $filter
     *
     * @throws \InvalidArgumentException
     */
    protected function openFilter(NodeElement $filter)
    {
        if ($element = $filter->find('css', 'button')) {
            $element->click();
        } else {
            throw new \InvalidArgumentException(
                'Impossible to open filter or maybe its type is not yet implemented'
            );
        }
    }

    /**
     * Get grid filter from label name
     * @param string $filterName
     *
     * @throws \InvalidArgumentException
     * @return NodeElement
     */
    protected function getFilter($filterName)
    {
        $filter = $this->getElement('grid-filter')
                        ->find('css', sprintf('div.filter-item:contains("%s")', $filterName));

        if (!$filter) {
            throw new \InvalidArgumentException(
                sprintf('Couldn\'t find a filter for name "%s"', $filterName)
            );
        }

        return $filter;
    }
}
