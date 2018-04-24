<?php

namespace Pim\Behat\Decorator\Field;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\SpinException;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

class Select2Decorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Set the given value to the select2 field
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $values = '' !== $value ? explode(',', $value) : [];
        $this->prune();

        foreach ($values as $value) {
            $widget = $this->getWidget();
            $value = trim($value);

            $this->getSession()->executeScript(
                sprintf(
                    '$(\'#%s input[type="text"]\').val(\'%s\').trigger(\'input\');',
                    $this->getAttribute('id'),
                    $value
                )
            );

            $this->spin(function () use ($widget, $value) {
                $result = $widget->find('css', sprintf('.select2-result-label:contains("%s")', $value));

                if (null !== $result && $result->isVisible()) {
                    $result->click();

                    return true;
                }

                throw new SpinException(sprintf(
                    'Could not find any result available with value "%s" for attributes "%s"',
                    $value,
                    $this->getAttribute('data-name')
                ));
            }, sprintf('A result has been found for "%s", but it seems we can not click on it.', $value));
        }

        $this->spin(function () {
            $this->close();

            return true;
        }, 'Cannot close the select2 field');
    }

    /**
     * Removes all the elements of a Select2.
     * This function is allowed for select2 multi select and select2 simple select
     */
    public function prune()
    {
        $elements = array_reverse($this->findAll(
            'css',
            '.select2-choices li.select2-search-choice,'.
            'a.select2-choice'
        ));
        foreach ($elements as $element) {
            $this->spin(function () use ($element) {
                $closeElement = $element->find('css', '.select2-search-choice-close');
                if (null !== $closeElement && $closeElement->isVisible()) {
                    $closeElement->click();

                    return false === $element->isValid() || !$closeElement->isVisible();
                };

                return true;
            }, 'Element is unchanged after deletion');
        }
    }

    /**
     * Open the select2 dropdown.
     * - Simple select contains an arrow to open container
     * - Multi select just need a click on the main container to open it
     */
    public function open()
    {
        $openerElement = $this->find('css', '.select2-arrow');
        if (null === $openerElement) {
            $openerElement = $this->find('css', '.select2-search-field');
        }

        if (!$this->element->hasClass('select2-dropdown-open')) {
            $openerElement->click();
        }
    }

    /**
     * Close the select2 dropdown
     */
    public function close()
    {
        $selectElementExists = $this->find('css', '.select2-dropdown-open') !== null;

        if ($selectElementExists && false !== strstr($this->getAttribute('class'), 'select2-dropdown-open')) {
            $dropMask = $this->getBody()->find('css', '#select2-drop-mask');

            if (null !== $dropMask) {
                $dropMask->click();
            }
        }
    }

    /**
     * @return NodeElement
     */
    public function getWidget()
    {
        return $this->spin(function () {
            $this->open();

            $select2Widgets = $this->getBody()->findAll('css', '.select2-drop');

            $widget = null;
            foreach ($select2Widgets as $select2Widget) {
                if ($select2Widget->isVisible()) {
                    $widget = $select2Widget;
                }
            }

            return $widget;
        }, 'Could not find the select2 widget drop');
    }

    /**
     * Get the value of the the HTML select (value='xxx')
     *
     * @throws TimeoutException
     * @throws \Exception
     *
     * @return array
     */
    public function getCodes()
    {
        if (false !== strpos('select2-container-multi', $this->getAttribute('class'))) {
            throw new \Exception('Not implement yet');
        }

        $id = $this->getAttribute('id');
        $id = sprintf('#%s', substr($id, 5, strlen($id)));

        $select = $this->spin(function () use ($id) {
            return $this->getSession()->getPage()->find('css', $id);
        }, sprintf('Impossible to find the select element for the select2 %s', $id));

        return [$select->getValue()];
    }

    /**
     * Get the label of the options displayed in the select2
     *
     * @throws TimeoutException
     * @throws \Exception
     *
     * @return array
     */
    public function getValues()
    {
        if (false !== strpos('select2-container-multi', $this->getAttribute('class'))) {
            throw new \Exception('Not implement yet');
        }

        $element = $this->spin(function () {
            return $this->find('css', '.select2-chosen');
        }, 'Impossible to find the open of the the select2');

        return [$element->getHtml()];
    }

    /**
     * Return the available elements in the Select2 dropdown element.
     * Note: if this Select2 is paginated, only return the first X elements visible on the 1st page.
     *
     * @throws TimeoutException
     *
     * @return array
     */
    public function getAvailableValues()
    {
        $widget = $this->getWidget();
        $results = [];

        $resultElements = $this->spin(function () use ($widget) {
            return $widget->findAll('css', '.select2-result-label, .select2-no-results');
        }, 'Cannot find any .select2-result-label nor select2-no-results element.');

        // Maybe a "No matches found"
        $firstResult = $resultElements[0];
        $noMatchesFound = $firstResult->hasClass('select2-no-results');

        if ($noMatchesFound) {
            return $results;
        }

        foreach ($resultElements as $element) {
            $results[] = $element->getText();
        }

        $this->spin(function () {
            $this->close();

            return true;
        }, 'Cannot close the select2 field');

        return $results;
    }

    /**
     * Type in a text in the search input of this select2 widget.
     *
     * @param string $text
     */
    public function search($text)
    {
        $widget = $this->getWidget();
        $widgetClasses = '.' . str_replace(' ', '.', $widget->getAttribute('class'));

        $text = trim($text);

        $this->getSession()->executeScript(
            sprintf(
                '$(\'%s .select2-search input[type="text"]\')' .
                '.val(\'%s\')' .
                '.trigger(\'input\');',
                $widgetClasses,
                $text
            )
        );
    }

    /**
     * Get the current value for the Select2
     *
     * @return string
     */
    public function getCurrentValue()
    {
        $input = $this->spin(function () {
            return $this->find('css', '.select2-selection-label-view');
        }, 'Cannot find the Select2 current selection input');

        return $input->getText();
    }
}
