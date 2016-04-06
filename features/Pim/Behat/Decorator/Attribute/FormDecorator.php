<?php

namespace Pim\Behat\Decorator\Attribute;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $attributeTypes = [
        'date'        => ['akeneo-datepicker-field'],
        'media'       => ['akeneo-media-uploader-field'],
        'metric'      => ['akeneo-metric-field'],
        'multiSelect' => ['akeneo-multi-select-field', 'akeneo-multi-select-reference-data-field'],
        'number'      => ['akeneo-number-field'],
        'price'       => ['akeneo-price-collection-field'],
        'select'      => ['akeneo-simple-select-field', 'akeneo-simple-select-reference-data-field'],
        'text'        => ['akeneo-text-field'],
        'textArea'    => ['akeneo-textarea-field', 'akeneo-wysiwyg-field'],
        'switch'      => ['akeneo-switch-field'],
    ];

    protected $decorators = [
        'date'        => ['Pim\Behat\Decorator\Attribute\DateDecorator'],
        'media'       => ['Pim\Behat\Decorator\Attribute\MediaDecorator'],
        'metric'      => ['Pim\Behat\Decorator\Attribute\MetricDecorator'],
        'multiSelect' => ['Pim\Behat\Decorator\Attribute\MultiselectDecorator'],
        'number'      => ['Pim\Behat\Decorator\Attribute\NumberDecorator'],
        'price'       => ['Pim\Behat\Decorator\Attribute\PriceDecorator'],
        'select'      => ['Pim\Behat\Decorator\Attribute\SelectDecorator'],
        'text'        => ['Pim\Behat\Decorator\Attribute\TextDecorator'],
        'textArea'    => ['Pim\Behat\Decorator\Attribute\TextareaDecorator'],
        'switch'      => ['Pim\Behat\Decorator\Attribute\SwitchDecorator'],
    ];

    protected $selectors = [
        'Attribute label' => '.field-container header label:contains("%s")',
        'Input element' => '.field-input input, .field-input textarea',
    ];

    /**
     * @param string $label Example: "Description", "Price in USD" or "Name"
     * @param bool   $copy
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     *
     * TODO: protected after refactoring?
     */
    public function findAttribute($label, $copy = false)
    {
        $selector = $this->selectors['Input element'];

        $attributeLabel = $label;
        if (preg_match('/^(?P<label>.*) in (?P<currency>.{1,3})$/', $attributeLabel, $matches)) {
            $selector = sprintf('input[data-currency=%s]', $matches['currency']);
            $attributeLabel = $matches['label'];
        }

        $fieldContainer = $this->findFieldContainer($attributeLabel);

        $formField = $this->spin(function () use ($fieldContainer, $copy) {
            return $fieldContainer->find('css', $copy ? '.copy-container .form-field' : '.form-field');
        }, sprintf('Cannot find the form field for label "%s"', $label));

        $inputElement = $this->spin(function () use ($formField, $selector) {
            return $formField->find('css', $selector);
        }, sprintf('Cannot find the input element for label "%s"', $label));

        return $inputElement;
    }

    /**
     * Find field container
     *
     * @param string $label
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findFieldContainer($label)
    {
        $selector = sprintf($this->selectors['Attribute label'], $label);

        $labelNode = $this->spin(function () use ($label, $selector) {
            return $this->find('css', $selector);
        }, sprintf('Cannot find the input label "%s" (%s)', $label, $selector));

        $container = $this->spin(function () use ($labelNode) {
            return $labelNode->getParent()->getParent()->getParent();
        });

        $container->name = $label;

        return $container;
    }

    /**
     * This method allows to fill a field by passing the label
     *
     * @param string  $label
     * @param string  $value
     *
     * @throws ElementNotFoundException
     */
    public function fillAttribute($label, $value)
    {
        $fieldContainer = $this->findFieldContainer($label);
        $fieldType = $this->getFieldType($fieldContainer);

        if (null === $fieldType) {
            throw new ElementNotFoundException($this->getSession());
        }

        $this->decorate($fieldContainer, $this->decorators[$fieldType]);

        $fieldContainer->fill($value);
    }

    /**
     * Guesses the type of field identified by $label and returns it.
     *
     * Possible identified fields are :
     * [date, metric, multiSelect, number, price, select, text, textArea]
     *
     * @param $fieldContainer
     *
     * @return string|null
     */
    protected function getFieldType($fieldContainer)
    {
        if (null === $fieldContainer || !$fieldContainer instanceof NodeElement) {
            return null;
        }

        $formFieldWrapper = $fieldContainer->find('css', 'div.form-field');

        foreach ($this->attributeTypes as $type => $classes) {
            foreach ($classes as $class) {
                if ($formFieldWrapper->hasClass($class)) {
                    return $type;
                };
            }
        }

        return null;
    }

    /**
     * @param $inputLabel
     *
     * @return array|string
     */
    public function getAttributeValue($inputLabel)
    {
        $fieldContainer = $this->findFieldContainer($inputLabel);
        $fieldType      = $this->getFieldType($fieldContainer);

        $this->decorate($fieldContainer, $this->decorators[$fieldType]);

        $fieldContainer->getValue();





        switch ($fieldType) {
            case 'textArea':
                $actual = $this->getTextAreaFieldValue($fieldContainer);
                break;
            case 'metric':
                $actual = $this->getMetricFieldValue($fieldContainer);
                break;
            case 'multiSelect':
                $actual   = $this->getMultiSelectFieldValue($fieldContainer);
                sort($actual);
                $actual   = implode(', ', $actual);
                break;
            case 'select':
                $actual = $this->getSelectFieldValue($fieldContainer);
                break;
            case 'media':
                $actual = $this->getMediaFieldValue($fieldContainer);
                break;
            case 'switch':
                $actual   = $this->isSwitchFieldChecked($fieldContainer);
                break;
            case 'text':
            case 'date':
            case 'number':
            case 'price':
            default:
                $actual = $this->findAttribute($inputLabel)->getValue();
                break;
        }

        return $actual;
    }


    /**
     * Get remove link for attribute
     *
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        return $this->spin(function () use ($field) {
            $link = $this->find('css', sprintf('.control-group:contains("%s") .remove-attribute', $field));
            if (!$link) {
                $link = $this->find('css', sprintf('.field-container:contains("%s") .remove-attribute', $field));
            }

            return $link;
        }, sprintf('Can not find remove link for attribute "%s".', $field));
    }

    /**
     * Returns the current value of a textarea
     * Handles both simple textarea and wysiwyg editor
     *
     * @param NodeElement $subContainer
     *
     * @return string
     */
    protected function getTextAreaFieldValue(NodeElement $subContainer)
    {
        $field = $subContainer->find('css', '.field-input textarea');

        if (!$field || !$field->isVisible()) {
            // the textarea can be hidden (display=none) when using WYSIWYG
            $div = $subContainer->find('css', '.note-editor > .note-editable');

            return $div->getHtml();
        } else {
            return $field->getValue();
        }
    }

    /**
     * Return the current formatted value of a metric field (e.g.: '4 KILOGRAM')
     *
     * @param NodeElement $subContainer
     *
     * @return string
     */
    protected function getMetricFieldValue(NodeElement $subContainer)
    {
        $input  = $subContainer->find('css', '.data');
        $select = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', '.select2-container');
        });

        return sprintf(
            '%s %s',
            $input->getValue(),
            $select->find('css', '.select2-chosen')->getText()
        );
    }

    /**
     * Return the current values of a multi-select field
     *
     * @param NodeElement $subContainer
     *
     * @return array
     */
    protected function getMultiselectFieldValue(NodeElement $subContainer)
    {
        $input = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', 'input[type="hidden"].select-field');
        });

        return '' === $input->getValue() ? [] : explode(',', $input->getValue());
    }

    /**
     * Return the current value of a select field
     *
     * @param NodeElement $subContainer
     *
     * @return string
     */
    protected function getSelectFieldValue(NodeElement $subContainer)
    {
        $input = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', 'input[type="hidden"].select-field');
        });

        return $input->getValue();
    }

    /**
     * Return the current filename uploaded in a media field
     *
     * @param NodeElement $subContainer
     *
     * @return string
     */
    protected function getMediaFieldValue(NodeElement $subContainer)
    {
        $widget = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', '.field-input .media-uploader');
        });

        $filenameNode = $widget->find('css', '.filename');

        return $filenameNode ? $filenameNode->getText() : '';
    }

    /**
     * Return the state of a switch field
     *
     * @param NodeElement $fieldContainer
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function isSwitchFieldChecked(NodeElement $fieldContainer)
    {
        $widget = $this->spin(function () use ($fieldContainer) {
            return $fieldContainer->find('css', '.field-input .switch.has-switch');
        }, sprintf('Cannot find the switch'));

        if ($widget->find('css', '.switch-on')) {
            return true;
        }
        if ($widget->find('css', '.switch-off')) {
            return false;
        }

        throw new \LogicException(sprintf('Switch "%s" is in an undefined state', $fieldContainer->name));
    }
}
