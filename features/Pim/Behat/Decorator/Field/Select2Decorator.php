<?php

namespace Pim\Behat\Decorator\Field;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class Select2Decorator extends ElementDecorator
{
    use SpinCapableTrait;

    public function setValue($value)
    {
        $values            = explode(', ', $value);
        $autocompleteField = $this->find('css', '.select2-input');

        // We close then reopen the widget to be sure we are always in the same state
        $this->closeSelect2();
        $this->find('css', '.select2-choices')->click();

        // TODO: handle choices deletion, see vendor/akeneo/pim-community-dev/features/Context/Page/Base/Form.php:709

        // The select2 plugin can put many widgets in the DOM.
        // We have to find the one that is visible and active.
        $select2Widgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.select2-drop');
        }, sprintf('Could not find any select2 widget for filter "%s"', $this->getAttribute('data-name')));

        $widget = null;
        foreach ($select2Widgets as $select2Widget) {
            if ($select2Widget->isVisible()) {
                $widget = $select2Widget;
            }
        }

        if (null === $widget) {
            throw new \Exception(
                sprintf('Could not find the select2 widget for filter "%s"', $this->getAttribute('data-name'))
            );
        }

        foreach ($values as $value) {
            $autocompleteField->setValue($value);

            $result = $this->spin(function () use ($widget, $value) {
                return $widget->find('css', sprintf('.select2-result-label:contains("%s")', $value));
            }, sprintf(
                'Could not find any result available with value "%s" for attributes "%s"',
                $value,
                $this->getAttribute('data-name')
            ));

            $result->click();


//            $autocompleteField->setValue($value);
        }
    }

    public function closeSelect2()
    {
        $dropMask = $this->getBody()->find('css', '#select2-drop-mask');

        if (null !== $dropMask) {
            $dropMask->click();
        }
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
