<?php

namespace Pim\Behat\Decorator\Common;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorate add attribute by group element
 *
 * @todo Introduce abstract select decorator based on current structure
 * and reuse as base class refactoring all select implementations (@a2xchip)
 */
class SelectGroupDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    const NOT_FOUND_MESSAGE = 'No matches found';

    protected $elements = [
        'searchSelector'     => '.add-attribute-group .select2-search input[type="text"]',
        'dropListBtn'        => '.add-attribute-group a.select2-choice',
        'resultList'         => '.add-attribute-group .select2-results',
        'resultItemSelector' => '.select2-result-label span:contains("%s"), li.select2-no-results',
    ];

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
     * return mixed
     */
    public function findItem($item)
    {
        $result = $this->openDropList()
            ->evaluateSearch($item)
            ->getResultForSearch($item);

        $this->closeDropList();

        return $result;
    }

    protected function openDropList()
    {
        $selector = $this->spin(function () {
            return $this->find('css', $this->elements['dropListBtn']);
        }, 'Cannot find drop list button');

        $selector->click();

        return $this;
    }

    /**
     * Evaluates search
     *
     * @param string $query
     */
    protected function evaluateSearch($query)
    {
        $this->getSession()->evaluateScript(
            "jQuery('" .
            $this->elements['searchSelector'] .
            "').val('" .
            preg_replace('/[\[\]]/u', '', $query) .
            "').trigger('input');"
        );

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
                    sprintf($this->elements['resultItemSelector'], $query)
                );
        }, 'Cannot find element in the attribute groups list');

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
            return $this->find('css', $this->elements['resultList']);
        }, 'Cannot find the result list element');
    }

    /**
     * Adds items that was selected
     */
    protected function addSelectedItems()
    {
        $this->find('css', '.ui-multiselect-footer button')->press();

        return $this;
    }

    /**
     * Close selects drop list
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
