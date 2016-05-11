<?php

namespace Pim\Behat\Decorator\Field;

use Pim\Behat\Decorator\ElementDecorator;

class MultiSelectDecorator extends ElementDecorator
{
    public function setValue($value)
    {
        $multiSelectWidget = $this->getBody()->find('css', '.select-filter-widget.dropdown-menu');

        $search = $multiSelectWidget->find('css', 'input[type="search"]');
        $search->setValue($value);

        $option = $multiSelectWidget->find('css', sprintf('li label:contains("%s")', $value));
        $option->click();
    }

    protected function getBody()
    {
        $element = $this;

        while('body' !== $element->getTagName()) {
            $element = $element->getParent();
        }

        return $element;
    }
}
