<?php

namespace Pim\Behat\Decorator\Common;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Abstract class for add select implementations
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractAddSelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    const NOT_FOUND_MESSAGE = 'No matches found';

    /**
     * Check and add list of given items
     *
     * @param array $items
     */
    public function addItems(array $items)
    {
        $this->openDropList();

        foreach ($items as $item) {
            $el = $this->evaluateSearch($item)
                ->getResultForSearch($item);

            if (null !== $el) {
                $el->getParent()->click();
            }
        }
        $this->addSelectedItems()
            ->closeDropList();
    }

    /**
     * @param string $item
     *
     * @return mixed
     */
    public function findItem($item)
    {
        $result = $this->openDropList()
            ->evaluateSearch($item)
            ->getResultForSearch($item);

        $this->closeDropList();

        return $result;
    }

    /**
     * @var string baseClass
     */
    protected $baseClass = '';

    /**
     * Gets array of view elements
     *
     * @return array
     */
    protected function getElements()
    {
        return [
            'searchSelector'     => $this->baseClass . ' .select2-search input[type="text"]',
            'dropListBtn'        => $this->baseClass . ' a.select2-choice',
            'resultList'         => $this->baseClass . ' .select2-results',
            'resultItemSelector' => '.select2-result-label span:contains("%s"), li.select2-no-results',
        ];
    }

    /**
     * Opens drop list
     *
     * @return $this
     */
    protected function openDropList()
    {
        $selector = $this->spin(function () {
            return $this->find('css', $this->getElements()['dropListBtn']);
        }, 'Cannot find drop list button');

        $selector->click();

        return $this;
    }

    /**
     * Evaluates search
     *
     * @param string $query
     *
     * @return $this
     */
    protected function evaluateSearch($query)
    {
        $script = "jQuery('" . $this->getElements()['searchSelector'] .
            "').val('" . preg_replace('/[\[\]]/u', '', $query) .
            "').trigger('input');";

        $this->getSession()->evaluateScript($script);

        return $this;
    }

    /**
     * Gets result of search
     *
     * @param string $query
     *
     * @return NodeElement|null
     */
    protected function getResultForSearch($query)
    {
        $list = $this->getResultListElement();
        $searchResult = $this->spin(function () use ($query, $list) {
            return $list->findAll(
                'css',
                sprintf($this->getElements()['resultItemSelector'], $query)
            );
        }, 'Cannot find element in the list');

        if (0 === count($searchResult)) {
            return null;
        }

        if (self::NOT_FOUND_MESSAGE == $searchResult[0]->getText()) {
            return null;
        }

        if (1 < count($searchResult)) {
            return $this->chooseSearched($searchResult, $query);
        }

        return $searchResult[0];
    }

    /**
     * Gets container holding search results
     *
     * @return mixed
     */
    protected function getResultListElement()
    {
        return $this->spin(function () {
            return $this->find('css', $this->getElements()['resultList']);
        }, 'Cannot find the result list element');
    }

    /**
     * Adds items that was selected
     *
     * return $this
     */
    protected function addSelectedItems()
    {
        $this->find('css', '.ui-multiselect-footer button')->press();

        return $this;
    }

    /**
     * Close selects drop list
     *
     * @return $this
     */
    protected function closeDropList()
    {
        $this->getSession()->evaluateScript('jQuery(\'#select2-drop-mask\').click();');

        return $this;
    }

    /**
     * @param array  $searchResults
     * @param string $query
     *
     * @return null|NodeElement
     */
    protected function chooseSearched($searchResults, $query)
    {
        $searched = null;

        foreach ($searchResults as $result) {
            if ($query === $result->getText()) {
                $searched = $result;
                break;
            }
        }

        return $searched;
    }
}
