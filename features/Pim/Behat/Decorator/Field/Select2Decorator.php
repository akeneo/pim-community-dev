<?php

namespace Pim\Behat\Decorator\Field;

use Context\Spin\SpinCapableTrait;
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

        $widget = $this->getWidget();
        foreach ($values as $value) {
            $value = trim($value);

            $this->getSession()->executeScript(
                sprintf(
                    '$(\'#%s input[type="text"]\').val(\'%s\').trigger(\'input\');',
                    $this->getAttribute('id'),
                    $value
                )
            );

            $result = $this->spin(function () use ($widget, $value) {
                return $widget->find('css', sprintf('.select2-result-label:contains("%s")', $value));
            }, sprintf(
                'Could not find any result available with value "%s" for attributes "%s"',
                $value,
                $this->getAttribute('data-name')
            ));

            $result->click();
        }

        $this->close();
    }

    public function prune()
    {
        $elements = array_reverse($this->findAll('css', '.select2-choices li.select2-search-choice'));
        foreach ($elements as $element) {
            $element->find('css', '.select2-search-choice-close')->click();
        }
    }

    /**
     * Open the select2 dropdown
     */
    public function open()
    {
        $this->find('css', '.select2-choices')->click();
    }

    /**
     * Close the select2 dropdown
     */
    public function close()
    {
        if (false !== strstr($this->getAttribute('class'), 'select2-dropdown-open')) {
            $dropMask = $this->getBody()->find('css', '#select2-drop-mask');

            if (null !== $dropMask) {
                $dropMask->click();
            }
        }
    }

    public function getWidget()
    {
        return $this->spin(function() {
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
