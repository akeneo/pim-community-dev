<?php

namespace Pim\Behat\Decorator\Field;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class MultiSelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    public function setValue($value)
    {
        // The multiselect plugin can put many widgets in the DOM.
        // We have to find the one that is visible and active.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.select-filter-widget.dropdown-menu');
        }, sprintf('Could not find any multiselect widget for filter "%s"', $this->getAttribute('data-name')));

        $widget = null;
        foreach ($multiSelectWidgets as $multiSelectWidget) {
            $widget = $multiSelectWidget->isVisible() ? $multiSelectWidget : null;
        }

        if (null === $widget) {
            throw new \Exception(
                sprintf('Could not find the multiselect widget for filter "%s"', $this->getAttribute('data-name'))
            );
        }

        // The search input for a multiselect is optional
        $search = $widget->find('css', 'input[type="search"]');
        if (null !== $search) {
            $search->setValue($value);
        }

        $option = $widget->find('css', sprintf('li label:contains("%s")', $value));
        $option->click();
    }

    /**
     * Get the <body> NodeElement
     *
     * @return NodeElement
     */
    protected function getBody()
    {
        $element = $this;

        while('body' !== $element->getTagName()) {
            $element = $element->getParent();
        }

        return $element;
    }
}
