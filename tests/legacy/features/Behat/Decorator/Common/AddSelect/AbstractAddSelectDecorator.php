<?php

namespace Pim\Behat\Decorator\Common\AddSelect;

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
abstract class AbstractAddSelectDecorator extends ElementDecorator implements AddSelectInterface
{
    use SpinCapableTrait;

    const NOT_FOUND_MESSAGE = 'No matches found';

    /**
     * @var string baseClass
     */
    protected $baseClass = '';

    /**
     * {@inheritdoc}
     */
    public function addOptions(array $options)
    {
        $this->openDropList();
        foreach ($options as $option) {
            $this->spin(function () use ($option) {
                $el = $this->evaluateSearch($option)
                    ->getResultForSearch($option);

                if (null !== $el) {
                    $el->getParent()->click();

                    return true;
                }

                return false;
            }, sprintf('Cannot find option "%s"', $option));
        }

        $this->addSelectedItems();
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($item)
    {
        $result = $this->openDropList()
            ->evaluateSearch($item)
            ->getResultForSearch($item);

        $this->closeDropList();

        return (bool) $result;
    }

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
        $this->spin(function () {
            $button = $this->find('css', $this->getElements()['dropListBtn']);

            if (null !== $button) {
                $button->click();

                return true;
            }

            return false;
        }, 'Cannot open drop list');

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
        $searchResults = $this->spin(function () use ($query) {
            return $this->findAll(
                'css',
                sprintf($this->getElements()['resultItemSelector'], $query)
            );
        }, 'Cannot find element in the list');

        if (0 === count($searchResults) || self::NOT_FOUND_MESSAGE === $searchResults[0]->getText()) {
            return null;
        }

        return $this->chooseSearched($searchResults, $query);
    }

    /**
     * Adds items that was selected
     *
     * return $this
     */
    protected function addSelectedItems()
    {
        $this->spin(function () {
            $multiselectButtons = $this->findAll('css', '.ui-multiselect-footer button');

            foreach ($multiselectButtons as $button) {
                if ($button->isVisible()) {
                    return $button;
                }
            }

            return null;
        }, 'Can not find any multiselect apply button')->press();


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
        foreach ($searchResults as $result) {
            if ($this->matchesSearch($result, $query)) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Checks if the query match the search
     *
     * @param NodeElement $el
     * @param string      $query
     *
     * @return bool
     */
    protected function matchesSearch($el, $query)
    {
        return $query === $el->getText() ||
            sprintf("[%s]", $query) === $el->getText();
    }
}
