<?php

namespace Pim\Behat\Decorator\Field;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class MultiSelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Set the given value to the multi select
     *
     * @throws \Exception
     *
     * @param string $value
     */
    public function setValue($value)
    {
        // The multiselect plugin can put many widgets in the DOM.
        // We have to find the one that is visible and active.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.select-filter-widget');
        }, sprintf('Could not find any multiselect widget for filter "%s"', $value));

        $visibleWidgets = array_filter($multiSelectWidgets, function ($widget) {
            return $widget->isVisible();
        });

        if (empty($visibleWidgets)) {
            throw new \Exception(
                sprintf('Could not find the multiselect widget for filter "%s"', $value)
            );
        }
        $widget = end($visibleWidgets);
        $values = '' !== $value ? explode(',', $value) : [];

        // The search input for a multiselect is optional
        $search = $widget->find('css', 'input[type="search"]');
        foreach ($values as $value) {
            $value = trim($value);
            if (null !== $search) {
                $search->setValue($value);
            }

            $option = $this->spin(function () use ($widget, $value) {
                return $widget->find('css', sprintf('li label:contains("%s")', $value));
            }, sprintf('Cannot find option "%s"', $value));
            $option->click();
        }
    }
}
