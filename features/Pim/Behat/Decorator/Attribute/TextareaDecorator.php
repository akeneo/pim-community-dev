<?php

namespace Pim\Behat\Decorator\Attribute;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextareaDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $value
     */
    public function fill($value)
    {
        $this->spin(function () use ($value) {
            $field = $this->find('css', 'div.field-input > textarea');

            if (null === $field || !$field->isVisible()) {
                // the textarea can be hidden (display=none) when using WYSIWYG
                $field = $this->find('css', 'div.note-editor > .note-editable');
            }

            $field->setValue($value);

            return ($field->getValue() === $value || $field->getHtml() === $value);
        });

        $this->getSession()->executeScript('$(\'.field-input textarea\').trigger(\'change\');');
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO
    }
}
