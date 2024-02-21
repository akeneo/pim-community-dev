<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\MultiSelectDecorator;

class ChoiceDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $field = $this->spin(function () {
            return $this->find('css', '.filter-select');
        }, sprintf('Cannot find the value field for the filter "%s"', $this->getAttribute('data-name')));

        $field = $this->decorate($field, [MultiSelectDecorator::class]);
        $field->setValue($value);

        $this->close();
    }

    /**
     * Get all available values in this filter
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAvailableValues()
    {
        // The multiselect plugin can put many widgets in the DOM.
        // We have to find the one that is visible and active.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.ui-multiselect-menu.select-filter-widget');
        }, 'Could not find any multiselect widget');

        $visibleWidgets = array_filter($multiSelectWidgets, function ($widget) {
            return $widget->isVisible();
        });

        if (empty($visibleWidgets)) {
            throw new \Exception('Could not find the multiselect widget');
        }
        $widget = end($visibleWidgets);

        $options = $this->spin(function () use ($widget) {
            return $widget->findAll('css', 'li span');
        }, 'Cannot find options');

        $values = [];
        foreach ($options as $option) {
            $values[] = $option->getText();
        }

        return array_filter($values);
    }

    /**
     * Closes the current choice filter
     */
    public function close()
    {
        $this->getSession()->executeScript("$(document)[0].dispatchEvent(new Event('mousedown'))");
    }
}
