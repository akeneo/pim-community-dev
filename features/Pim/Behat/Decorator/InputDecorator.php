<?php

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InputDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /*
     * The Text inputs selectors contains an EE selector.
     * This should not be declared here, but we wait for the Decorators refactoring to choose where to declare it..
     * TODO Move .asset-collection-picker into EE.
     */
    protected $selectors = [
        'Input label' => ['css' => '.field-container header label:contains("%s")'],
        'Text inputs' => ['css' => '.field-input input, .field-input textarea, .field-input .asset-collection-picker'],
    ];

    /**
     * @param string $label
     * @param bool   $copy
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findField($label, $copy = false)
    {
        $matches = null;
        if (preg_match('/^(?P<amount>.*) in (?P<currency>[A-Z]{1,3})$/', $label, $matches)) {
            $label    = $matches['amount'];
            $currency = $matches['currency'];
            $fieldContainer = $this->findFieldContainer($label);

            return $this->findCompoundField($fieldContainer, $currency);
        }

        $subContainer = $this->spin(function () use ($label, $copy) {
            return $this->findFieldContainer($label)
                ->find('css', $copy ? '.copy-container .form-field' : '.form-field');
        });

        $field = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', $this->selectors['Text inputs']['css']);
        });

        return $field;
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

        $selector = sprintf($this->selectors['Input label']['css'], $label);

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
     * @param Element $element
     *
     * @throws ElementNotFoundException
     */
    public function fillField($label, $value, Element $element = null)
    {
        $isLabel = false;

        try {
            $fieldContainer = $this->findFieldContainer($label);
        } catch (ElementNotFoundException $e) {
            $isLabel        = true;
            $fieldContainer = $this->extractLabelElement($label, $element);
        }

        $fieldType = $this->getFieldType($fieldContainer, $isLabel);

        switch ($fieldType) {
            case 'text':
            case 'date':
            case 'number':
                $this->fillTextField($fieldContainer, $value);
                break;
            case 'textArea':
                $this->fillTextAreaField($fieldContainer, $value);
                break;
            case 'metric':
                $this->fillMetricField($fieldContainer, $value);
                break;
            case 'multiSelect':
                $this->fillMultiSelectField($fieldContainer, $value);
                break;
            case 'price':
                $this->fillPriceField($fieldContainer, $value);
                break;
            case 'select':
                $this->fillSelectField($fieldContainer, $value);
                break;
            default:
                throw new ElementNotFoundException($this->getSession());
        }
    }

    /**
     * Fills a text field element with $value, identified by its container or label.
     *
     * @param NodeElement $fieldContainerOrLabel
     * @param string      $value
     *
     * @throws ElementNotFoundException
     */
    protected function fillTextField(NodeElement $fieldContainerOrLabel, $value)
    {
        $field = $fieldContainerOrLabel->find('css', 'div.field-input input');

        // no field found, we're using a label
        if (null === $field) {
            $field = $fieldContainerOrLabel->getParent()->getParent()->find('css', 'div.field-input input');
        }

        if (null === $field) {
            $field = $fieldContainerOrLabel->getParent()->find('css', 'div.controls input');
        }

        if (null === $field) {
            throw new ElementNotFoundException($this->getSession(), 'text field');
        }

        $field->setValue($value);
        $this->getSession()->executeScript('$(\'.field-input input[type="text"]\').trigger(\'change\');');
    }

    /**
     * Find a compound field
     *
     * @param NodeElement $fieldContainer
     * @param string      $currency
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findCompoundField($fieldContainer, $currency)
    {
        $input = $this->spin(function () use ($fieldContainer, $currency) {
            return $fieldContainer->find('css', sprintf('input[data-currency=%s]', $currency));
        }, sprintf(
            'Cannot find the compound field with currency %s in container %s',
            $currency,
            $fieldContainer->name
        ));

        return $input;
    }

    /**
     * Fills a textarea field element with $value
     *
     * @param NodeElement $fieldContainer
     * @param string      $value
     */
    protected function fillTextAreaField(NodeElement $fieldContainer, $value)
    {
        $this->spin(function () use ($value, $fieldContainer) {
            $field = $fieldContainer->find('css', 'div.field-input > textarea');

            if (!$field || !$field->isVisible()) {
                // the textarea can be hidden (display=none) when using WYSIWYG
                $field = $fieldContainer->find('css', 'div.note-editor > .note-editable');
            }

            $field->setValue($value);

            return ($field->getValue() === $value || $field->getHtml() === $value);
        });

        $this->getSession()->executeScript('$(\'.field-input textarea\').trigger(\'change\');');
    }

    /**
     * Fills a simple select2 field with $value
     *
     * @param NodeElement $fieldContainer
     * @param string      $value
     *
     * @throws ExpectationException
     */
    protected function fillSelectField(NodeElement $fieldContainer, $value)
    {
        if ('' === $value || null === $value) {
            $emptyLink = $this->spin(function () use ($fieldContainer) {
                return $fieldContainer->find('css', '.select2-search-choice-close');
            });

            $emptyLink->click();

            $this->getSession()->executeScript(
                '$(\'.field-input input[type="hidden"].select-field\').trigger(\'change\');'
            );

            return;
        }

        $link = $this->spin(function () use ($fieldContainer) {
            return $fieldContainer->find('css', 'a.select2-choice');
        }, sprintf('Could not find select2 widget inside %s', $fieldContainer->getParent()->getHtml()));

        $link->click();

        $item = $this->spin(function () use ($link, $value) {
            return $this->getSession()
                ->getPage()
                ->find('css', sprintf('.select2-results li:contains("%s")', $value));
        });

        $item->click();

        $this->getSession()->executeScript(
            '$(\'.field-input input[type="hidden"].select-field\').trigger(\'change\');'
        );

        return;
    }

    /**
     * Fills a metric field with $value
     *
     * @param NodeElement $fieldContainer
     * @param string      $value
     *
     * @throws \InvalidArgumentException
     */
    protected function fillMetricField(NodeElement $fieldContainer, $value)
    {
        if (false !== strpos($value, ' ')) {
            list($text, $select) = explode(' ', $value);
        } else {
            $text   = $value;
            $select = null;
        }

        $field = $fieldContainer->find('css', '.field-input');
        if (null !== $select) {
            $item = null;
            if (null !== $link = $field->find('css', 'a.select2-choice')) {
                $link->click();

                $item = $this->spin(function () use ($select) {
                    return $this->getSession()
                        ->getPage()
                        ->find('css', sprintf('#select2-drop li:contains("%s")', $select));
                }, sprintf('Could not find option "%".', $select));
            }

            if (!$item) {
                throw new \InvalidArgumentException(
                    sprintf('Could not find select2 widget inside %s', $field->getParent()->getHtml())
                );
            }

            $item->click();
        }

        $this->fillTextField($fieldContainer, $text);
    }

    /**
     * Fills a select2 multi-select field with $values
     *
     *
     * @param NodeElement $fieldContainer
     * @param string      $values
     *
     * @throws \InvalidArgumentException
     */
    protected function fillMultiSelectField(NodeElement $fieldContainer, $values)
    {
        $field = $fieldContainer->find('css', '.form-field');

        // clear multi select first
        $fieldClasses = $field->getAttribute('class');
        if (preg_match('/akeneo-multi-select(-reference-data)?-field/', $fieldClasses, $matches)) {
            $select2Selector = sprintf('.%s div.field-input > input', $matches[0]);
            $script          = sprintf('$("%s").select2("val", "");$("%1$s").trigger("change");', $select2Selector);
            $this->getSession()->executeScript($script);
        }

        $link = $fieldContainer->find('css', 'ul.select2-choices');
        if (null === $link) {
            throw new \InvalidArgumentException(
                sprintf('Could not find select2 widget inside %s', $fieldContainer->getParent()->getHtml())
            );
        }

        foreach ($this->listToArray($values) as $value) {
            $link->click();
            $item = $this->spin(function () use ($value) {
                return $this->getSession()
                    ->getPage()
                    ->find(
                        'css',
                        sprintf('.select2-result:not(.select2-selected) .select2-result-label:contains("%s")', $value)
                    );
            });

            // Select the value in the displayed dropdown
            if (null !== $item) {
                $item->click();
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Could not find select2 item with value %s inside %s', $value, $link->getHtml())
                );
            }
        }

        $this->getSession()->executeScript(
            '$(\'.field-input input.select-field\').trigger(\'change\');'
        );
    }

    /**
     * Fills a compound field with $value, by passing the $label
     *
     * @param NodeElement $fieldContainer
     * @param string      $value
     *
     * @throws ElementNotFoundException
     */
    protected function fillPriceField(NodeElement $fieldContainer, $value)
    {
        $amount   = null;
        $currency = null;

        if (false !== strpos($value, ' ')) {
            list($amount, $currency) = explode(' ', $value);
        }

        if (null === $currency) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "%s" field is compound but the sub label was not provided',
                    $fieldContainer->name
                )
            );
        }

        $field = $this->findCompoundField($fieldContainer, $currency);
        $field->setValue($amount);

        $this->getSession()->executeScript(
            '$(\'.field-input input[type="text"]\').trigger(\'change\');'
        );
    }

    /**
     * @param string $inputLabel
     * @param string $currency
     *
     * @return NodeElement
     */
    public function findCurrencyInput($inputLabel, $currency)
    {
        $fieldContainer = $this->findFieldContainer($inputLabel);

        return $this->findCompoundField($fieldContainer, $currency);
    }

    /**
     * Extracts and returns the label NodeElement, identified by $field content and $element
     *
     * @param string  $field
     * @param Element $element
     *
     * @return NodeElement
     */
    protected function extractLabelElement($field, $element)
    {
        $subLabelContent = null;
        $labelContent    = $field;

        $matches = null;
        if (preg_match('/^(?P<subLabel>.*) (?P<label>[A-Z]{1,3})$/', $field, $matches)) {
            $subLabelContent = $matches['subLabel'];
            $labelContent    = $matches['label'];
        }

        if ($element) {
            $label = $this->spin(function () use ($element, $labelContent) {
                return $element->find('css', sprintf('label:contains("%s")', $labelContent));
            }, sprintf('unable to find label %s in element : %s', $labelContent, $element->getHtml()));
        } else {
            $label = $this->spin(function () use ($labelContent) {
                return $this->find('css', sprintf('label:contains("%s")', $labelContent));
            }, sprintf('unable to find label %s', $labelContent));
        }

        $label->labelContent    = $labelContent;
        $label->subLabelContent = $subLabelContent;

        return $label;
    }

    /**
     * Transform a list to array
     *
     * @param string $list
     *
     * @return array
     */
    protected function listToArray($list)
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
            return '';
        }
    }

    /**
     * @param $inputLabel
     *
     * @return array|string
     */
    public function getInputValue($inputLabel)
    {
        $fieldContainer = $this->findFieldContainer($inputLabel);
        $fieldType      = $this->getFieldType($fieldContainer);

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
                $actual = $this->findField($inputLabel)->getValue();
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
