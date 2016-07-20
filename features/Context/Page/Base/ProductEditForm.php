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
                'Available attributes'            => [
                    'css'        => '.add-attribute',
                    'decorators' => ['Pim\Behat\Decorator\Common\AddAttributeDecorator']
                ],
                'Available attributes list'       => ['css' => '.add-attribute .select2-results'],
                'Available attributes search'     => ['css' => '.add-attribute .select2-search input[type="text"]'],
                'Select2 dropmask'                => ['css' => '.select2-drop-mask'],
            ]
        );
    }

    /**
     * @param string $attribute
     * @param string $group
     *
     * @return NodeElement|null
     */
    public function findAvailableAttributeInGroup($attribute, $group)
    {
        $searchSelector = $this->elements['Available attributes search']['css'];

        $selector = $this->spin(function () {
            return $this->find('css', $this->elements['Available attributes button']['css']);
        }, sprintf('Cannot find element "%s"', $this->elements['Available attributes button']['css']));

        // Open select2
        $selector->click();

        $list = $this->spin(function () {
            return $this->getElement('Available attributes list');
        }, 'Cannot find the attribute list element');

        // We NEED to fill the search field with jQuery to avoid the TAB key press (because of mink),
        // because select2 selects the first element on TAB key press.
        $this->getSession()->evaluateScript(
            "jQuery('" . $searchSelector . "').val('" . $attribute . "').trigger('input');"
        );

        $groupLabels = $this->spin(function () use ($list, $group) {
            return $list->findAll('css', sprintf('li .group-label:contains("%s"), li.select2-no-results', $group));
        }, 'Cannot find element in the attribute list');

        // Maybe a "No matches found"
        $firstResult = $groupLabels[0];
        $text = $firstResult->getText();
        $results = [];

        if ('No matches found' !== $text) {
            foreach ($groupLabels as $groupLabel) {
                $li = $groupLabel->getParent();
                $results[$li->find('css', '.attribute-label')->getText()] = $li;
            }
        }

        // Close select2
        $this->find('css', '#select2-drop-mask')->click();

        return isset($results[$attribute]) ? $results[$attribute] : null;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Used with the new 'add-attributes' module. The method should be in the Form parent
     * when legacy stuff is removed.
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $availableAttribute = $this->spin(function () {
            return $this->getElement('Available attributes');
        }, 'Cannot find the add attribute element');
        $availableAttribute->addAttributes($attributes);
    }

    /**
     * This method allows to fill a field by passing the label
     *
     * @param string  $label
     * @param string  $value
     * @param Element $element
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
                parent::fillField($fieldContainer->labelContent, $value);
                break;
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
        if (1 === preg_match('/ in (.{1,3})$/', $label)) {
            // Price in EUR
            $label = explode(' in ', $label)[0];
        }

        $labelNode = $this->spin(function () use ($label) {
            return $this->find('css', sprintf('.field-container header label:contains("%s")', $label));
        }, 'Cannot find the field label');

        $container = $this->spin(function () use ($labelNode) {
            return $labelNode->getParent()->getParent()->getParent();
        }, sprintf('Cannot find parents for "%s" field', $label));

        $container->name = $label;

        return $container;
    }

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
        if (1 === preg_match('/ in (.{1,3})$/', $label)) {
            // Price in EUR
            list($label, $currency) = explode(' in ', $label);
            $fieldContainer = $this->findFieldContainer($label);

            return $this->findCompoundField($fieldContainer, $currency);
        }

        $subContainer = $this->spin(function () use ($label, $copy) {
            return $this->findFieldContainer($label)
                ->find('css', $copy ? '.copy-container .form-field' : '.form-field');
        }, sprintf('Cannot find "%s" sub container', $label));

        $field = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', '.field-input input, .field-input textarea');
        }, sprintf('Cannot find ".field-input input" or ".field-input textarea" in sub container "%s"', $label));

        return $field;
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
                $id = $fieldContainer->find('css', 'textarea')->getAttribute('id');
                $this->getSession()->executeScript(
                    sprintf('$(\'#%s\').parent().find(".note-editable").html(\'%s\').trigger(\'change\');', $id, $value)
                );

                return true;
            }

            $field->setValue($value);

            return ($field->getValue() === $value || $field->getHtml() === $value);
        }, sprintf('Cannot fill the textarea with "%s"', $value));

        $this->getSession()->executeScript('$(\'.field-input textarea\').trigger(\'change\');');
    }

    /**
     * Fills a simple select2 field with $value
     *
     * @param NodeElement $fieldContainer
     * @param string      $value
     */
    protected function fillSelectField(NodeElement $fieldContainer, $value)
    {
        $element = $this->spin(function () use ($fieldContainer) {
            return $fieldContainer->find('css', '.select2-container');
        }, 'Can not find the select2 container.');

        $field = $this->decorate(
            $element,
            ['Pim\Behat\Decorator\Field\Select2Decorator']
        );

        $field->setValue($value);

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
            if (null !== $link = $field->find('css', 'a.select2-choice')) {
                $link->click();

                $item = $this->spin(function () use ($select) {
                    return $this->find('css', sprintf('#select2-drop li:contains("%s")', $select));
                }, sprintf('Cannot find "%s" in select2 widget', $value));
            }

            if (null === $item) {
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

        $link = $this->spin(function () use ($fieldContainer) {
            return $fieldContainer->find('css', 'ul.select2-choices');
        }, sprintf('Could not find select2 widget inside %s', $fieldContainer->getParent()->getHtml()));

        // clear multi select first
        $fieldClasses = $field->getAttribute('class');
        if (preg_match('/akeneo-multi-select(-reference-data)?-field/', $fieldClasses, $matches)) {
            $select2Selector = sprintf('.%s div.field-input > input', $matches[0]);
            $script          = sprintf('$("%s").select2("val", "");$("%1$s").trigger("change");', $select2Selector);
            $this->getSession()->executeScript($script);
        }

        foreach ($this->listToArray($values) as $value) {
            $link->click();
            $item = $this->spin(function () use ($value) {
                return $this->find(
                    'css',
                    sprintf('.select2-result:not(.select2-selected) .select2-result-label:contains("%s")', $value)
                );
            }, sprintf('Cannot find "%s" element in select2 widget', $value));

            $item->click();
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

        // it happens when we want to set an empty price
        if (null === $currency && null !== $value) {
            $currency = $value;
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
     * Find a compound field
     *
     * @param NodeElement $fieldContainer
     * @param             $currency
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findCompoundField($fieldContainer, $currency)
    {
        $input = $fieldContainer->find('css', sprintf('input[data-currency=%s]', $currency));
        if (!$input) {
            throw new ElementNotFoundException(
                $this->getSession(),
                'compound field',
                'id|name|label|value',
                $fieldContainer->name
            );
        }

        return $input;
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
        if (!$field) {
            $field = $fieldContainerOrLabel->getParent()->getParent()->find('css', 'div.field-input input');
        }

        if (!$field) {
            $field = $fieldContainerOrLabel->getParent()->find('css', 'div.controls input');
        }

        if (null === $field) {
            throw new ElementNotFoundException(sprintf(
                'No text field can be found from "%s".',
                $fieldContainerOrLabel->getText()
            ));
        }

        $field->setValue($value);
        $this->getSession()->executeScript('$(\'.field-input input[type="text"]\').trigger(\'change\');');
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
                    '.validation-errors .error-message:contains("%s")',
                    $text
                )
            );
        }, sprintf('Cannot find error message "%s" in validation tooltip', $text));
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

        if (null === $field || !$field->isVisible()) {
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
        }, 'Cannot find ".select2-container" in metric field');

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
        }, 'Cannot find ".select-field" in multiselect field');

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
        }, 'Cannot find ".select-field" in simple select field');

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
        }, 'Cannot find ".media-uploader" in media field');

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
        }, 'Cannot find ".switch.has-switch" in switch field');

        if ($widget->find('css', '.switch-on')) {
            return true;
        }
        if ($widget->find('css', '.switch-off')) {
            return false;
        }

        throw new \LogicException(sprintf('Switch "%s" is in an undefined state', $fieldContainer->name));
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $element = $this->getElement('Save');

        $this->spin(function () use ($element) {
            return $element->isVisible();
        }, 'Save button is not visible');

        $element->click();

        $this->spin(function () {
            return null === $this->find(
                'css',
                '*:not(.hash-loading-mask):not(.grid-container):not(.loading-mask) > .loading-mask'
            );
        }, 'The loading mask didn\'t disapeared');
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        return $this->spin(function () use ($field) {
            return $this->find('css', sprintf(
                '.control-group:contains("%s") .remove-attribute, .field-container:contains("%s") .remove-attribute',
                $field,
                $field
            ));
        }, sprintf('Spining to get remove link on product edit form for field "%s"', $field));
    }
}
