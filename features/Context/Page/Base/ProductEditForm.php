<?php

namespace Context\Page\Base;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\TimeoutException;

/**
 * Product Edit Form
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEditForm extends Form
{
    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Locales dropdown'                => ['css' => '.attribute-edit-actions .locale-switcher'],
                'Channel dropdown'                => ['css' => '.attribute-edit-actions .scope-switcher'],
                // Note: It erases parent add-attributes selector values because of the new JS module,
                // once refactoring done everywhere, it should be set in parent like before
                'Available attributes button'     => ['css' => '.add-attribute a.select2-choice'],
                'Available attributes list'       => ['css' => '.add-attribute .select2-results'],
                'Available attributes search'     => ['css' => '.add-attribute .select2-search input[type="text"]'],
                'Available attributes add button' => ['css' => '.add-attribute .ui-multiselect-footer button'],
                'Attribute inputs' => [
                    'css' => '.tab-pane.product-values',
                    'decorators' => [
                        'Pim\Behat\Decorator\InputDecorator'
                    ]
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fillField($field, $value, Element $element = null)
    {
        try {
            $this->getElement('Attribute inputs')->fillField($field, $value, $element);
        } catch (TimeoutException $e) {
            parent::fillField($field, $value, $element);
        } catch (ElementNotFoundException $e) {
            parent::fillField($field, $value, $element);
        }
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
        $matches = null;
        if (preg_match('/^(?P<amount>.*) in [A-Z]{1,3}$/', $label, $matches)) {
            // Match "Price in EUR" to have "Price" as label.
            $label = $matches['amount'];
        }

        try {
            $labelNode = $this->spin(function () use ($label) {
                return $this->find('css', sprintf('.field-container header label:contains("%s")', $label));
            }, 'Cannot find the field label');
        } catch (\Exception $e) {
            throw new ElementNotFoundException($this->getSession());
        }

        $container = $this->spin(function () use ($labelNode) {
            return $labelNode->getParent()->getParent()->getParent();
        });

        $container->name = $label;

        return $container;
    }

    /**
     * Transform a list to array
     *
     * @param string $list
     *
     * @return array
     */
    public function listToArray($list)
    {
        if (empty($list)) {
            return [];
        }

        return explode(', ', str_replace(' and ', ', ', $list));
    }

    /**
     * Guesses the type of field identified by $label and returns it.
     *
     * Possible identified fields are :
     * [date, metric, multiSelect, number, price, select, text, textArea]
     *
     * @param $fieldContainer
     *
     * @return string
     */
    protected function getFieldType($fieldContainer, $isLabel = false)
    {
        if (null === $fieldContainer || !$fieldContainer instanceof NodeElement) {
            return null;
        }

        if ($isLabel) {
            $formFieldWrapper = $fieldContainer->getParent()->getParent();
        } else {
            $formFieldWrapper = $fieldContainer->find('css', 'div.form-field');
        }

        if ($formFieldWrapper->hasClass('akeneo-datepicker-field')) {
            return 'date';
        } elseif ($formFieldWrapper->hasClass('akeneo-metric-field')) {
            return 'metric';
        } elseif ($formFieldWrapper->hasClass('akeneo-multi-select-field') ||
                  $formFieldWrapper->hasClass('akeneo-multi-select-reference-data-field')
        ) {
            return 'multiSelect';
        } elseif ($formFieldWrapper->hasClass('akeneo-number-field')) {
            return 'number';
        } elseif ($formFieldWrapper->hasClass('akeneo-price-collection-field')) {
            return 'price';
        } elseif ($formFieldWrapper->hasClass('akeneo-simple-select-field') ||
                  $formFieldWrapper->hasClass('akeneo-simple-select-reference-data-field')
        ) {
            return 'select';
        } elseif ($formFieldWrapper->hasClass('akeneo-text-field')) {
            return 'text';
        } elseif ($formFieldWrapper->hasClass('akeneo-textarea-field') ||
                  $formFieldWrapper->hasClass('akeneo-wysiwyg-field')
        ) {
            return 'textArea';
        } elseif ($formFieldWrapper->hasClass('akeneo-media-uploader-field')) {
            return 'media';
        } elseif ($formFieldWrapper->hasClass('akeneo-switch-field')) {
            return 'switch';
        } else {
            return parent::getFieldType($fieldContainer);
        }
    }

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function findValidationTooltip($text)
    {
        return $this->spin(function () use ($text) {
            return $this->find(
                'css',
                sprintf(
                    '.validation-errors span.error-message:contains("%s")',
                    $text
                )
            );
        });
    }

    /**
     * Checks if the specified field is set to the expected value, raises an exception if not
     *
     * Should be moved to a decorator
     *
     * @param string $label
     * @param string $expected
     * @param bool   $copy
     *
     * @throws ExpectationException
     */
    public function compareFieldValue($label, $expected, $copy = false)
    {
        $fieldContainer = $this->findFieldContainer($label);
        $fieldType      = $this->getFieldType($fieldContainer);
        $subContainer   = $fieldContainer->find('css', $copy ? '.copy-container .form-field' : '.form-field');

        switch ($fieldType) {
            case 'textArea':
                $actual = $this->getTextAreaFieldValue($subContainer);
                break;
            case 'metric':
                $actual = $this->getMetricFieldValue($subContainer);
                break;
            case 'multiSelect':
                $actual   = $this->getMultiSelectFieldValue($subContainer);
                $expected = $this->listToArray($expected);
                sort($actual);
                sort($expected);
                $actual   = implode(', ', $actual);
                $expected = implode(', ', $expected);
                break;
            case 'select':
                $actual = $this->getSelectFieldValue($subContainer);
                break;
            case 'media':
                $actual = $this->getMediaFieldValue($subContainer);
                break;
            case 'switch':
                $actual   = $this->isSwitchFieldChecked($subContainer);
                $expected = ('on' === $expected);
                break;
            case 'text':
            case 'date':
            case 'number':
            case 'price':
            default:
                $actual = $this->findField($label, $copy)->getValue();
                break;
        }

        if ($expected != $actual) {
            throw new ExpectationException(
                sprintf(
                    'Expected product field "%s" to contain "%s", but got "%s".',
                    $label,
                    $expected,
                    $actual
                ),
                $this->getSession()
            );
        }
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
        });

        if ($widget->find('css', '.switch-on')) {
            return true;
        }
        if ($widget->find('css', '.switch-off')) {
            return false;
        }

        throw new \LogicException(sprintf('Switch "%s" is in an undefined state', $fieldContainer->name));
    }
}
