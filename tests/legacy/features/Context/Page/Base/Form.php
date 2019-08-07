<?php

namespace Context\Page\Base;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinException;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use Pim\Behat\Decorator\Common\AddSelect\AttributeAddSelectDecorator;
use Pim\Behat\Decorator\Common\AddSelect\AttributeGroupAddSelectDecorator;
use Pim\Behat\Decorator\Common\DropdownMenuDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * Basic form page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Form extends Base
{
    use ClosestTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Dialog'                          => ['css' => 'div.modal'],
                'Associations list'               => ['css' => '.associations-list'],
                'Group selector'                  => ['css' => '.group-selector'],
                'Association type selector'       => [
                    'css'        => '.association-type-selector',
                    'decorators' => [DropdownMenuDecorator::class]
                ],
                'Tree selector'                   => ['css' => '.tree-selector'],
                'Target selector'                 => ['css' => '.target-selector'],
                'Attribute filter selector'       => ['css' => '.attribute-filter'],
                'Validation errors'               => ['css' => '.validation-tooltip'],
                'Available attributes form'       => ['css' => '#pim_available_attributes'],
                'Available attributes button'     => ['css' => 'button:contains("Add attributes")'],
                'Available attributes list'       => ['css' => '.pimmultiselect .ui-multiselect-checkboxes'],
                'Available attributes search'     => ['css' => '.pimmultiselect input[type="search"]'],
                'Available attributes add button' => ['css' => '.pimmultiselect a.btn:contains("Add")'],
                'Updates grid'                    => [
                    'css' => '.tab-pane.tab-history table.grid, .tab-container .history'
                ],
                'Save'                            => ['css' => '.AknButton--apply'],
                'Available attributes'            => [
                    'css'        => '.add-attribute',
                    'decorators' => [AttributeAddSelectDecorator::class]
                ],
                'Available groups'                  => [
                    'css'        => '.add-attribute-group',
                    'decorators' => [AttributeGroupAddSelectDecorator::class],
                ],
                'Tooltips'                        => ['css' => '.icon-info-sign'],
            ],
            $this->elements
        );
    }

    /**
     * Press the save button
     */
    public function save()
    {
        $this->spin(function () {
            $this->getElement('Save')->click();

            return true;
        }, 'Cannot click on the save button.');
    }

    /**
     * Press the save button
     */
    public function saveAndClose()
    {
        $this->pressButton('Save and close');
    }

    /**
     * Open the specified panel
     *
     * @param string $panel
     */
    public function openPanel($panel)
    {
        $elt = $this->spin(function () {
            return $this->getElement('Panel selector');
        }, 'Can not find the Panel selector');

        $panel = strtolower($panel);
        if (null === $elt->find('css', sprintf('button[data-panel$="%s"].active', $panel))) {
            $elt->find('css', sprintf('button[data-panel$="%s"]', $panel))->click();
        }
    }

    /**
     * Get the form tab containing $tab text
     *
     * @param string $tab
     *
     * @return NodeElement|null
     */
    public function getFormTab($tab)
    {
        $tabs = $this->getPageTabs();

        try {
            $node = $this->spin(function () use ($tabs, $tab) {
                return $tabs->find('css', sprintf('a:contains("%s")', $tab));
            }, sprintf('Cannot find form tab "%s"', $tab));
        } catch (\Exception $e) {
            $node = null;
        }

        return $node;
    }

    /**
     * Visit the specified group
     *
     * @param string $groupName
     * @param string $type
     */
    public function visitGroup($groupName, $type = 'Group')
    {
        $this->spin(function () use ($groupName, $type) {
            $loadingMasks = $this->findAll('css', '.loading-mask');
            if (0 < count(array_filter($loadingMasks, function ($loadingMask) {
                return $loadingMask->isVisible();
            }))) {
                return false;
            }
            $this->getGroup($groupName, $type)->click();
            $this->getGroup($groupName, $type)->click();

            return true;
        }, sprintf('Cannot visit group "%s"', $groupName));
    }

    /**
     * @param $filter
     */
    public function filterAttributes($filter)
    {
        $this->spin(function () use ($filter) {
            $loadingMasks = $this->findAll('css', '.loading-mask');
            if (0 < count(array_filter($loadingMasks, function ($loadingMask) {
                return $loadingMask->isVisible();
            }))) {
                return false;
            }

            $this->getGroup($filter, 'Attribute filter')->click();

            return true;
        }, sprintf('Cannot filter attributes with "%s"', $filter));
    }

    /**
     * @param $attributeGroup
     */
    public function clickOnAttributeGroupHeader($attributeGroup)
    {
        $this->spin(function () use ($attributeGroup) {
            $loadingMasks = $this->findAll('css', '.loading-mask');
            if (0 < count(array_filter($loadingMasks, function ($loadingMask) {
                return $loadingMask->isVisible();
            }))) {
                return false;
            }

            $groupHeader = $this->find('css', sprintf('.required-attribute-indicator[data-group="%s"]', $attributeGroup));

            if (null === $groupHeader) {
                return false;
            }

            $groupHeader->click();

            return true;
        }, sprintf('Cannot click on attribute group "%s" header', $attributeGroup));
    }

    /**
     * @param $groupName
     * @param string $type
     *
     * @return NodeElement
     *
     * //TODO: make it more generic, no logic is specific to groups here, it's just naming.
     */
    public function getGroup($groupName, $type = 'Group')
    {
        return $this->spin(function () use ($groupName, $type) {
            $groupSelector = $this->openGroupSelector($type);

            $groupLabels = $groupSelector->findAll('css', '.label');
            foreach ($groupLabels as $groupLabel) {
                if (strtolower(trim($groupLabel->getText())) === strtolower($groupName) && $groupLabel->isVisible()) {
                    return $this->getClosest($groupLabel, 'AknDropdown-menuLink');
                }
            }

            return false;
        }, sprintf('Cannot find the %s "%s"', $type, $groupName));
    }

    /**
     * Get the tabs in the current page
     *
     * @return NodeElement[]
     */
    public function getTabs()
    {
        return $this->spin(function () {
            return $this->find('css', $this->elements['Tabs']['css']);
        }, 'Cannot find the tab container')->findAll('css', 'a');
    }

    /**
     * Get the specified tab
     *
     * @param string $tab
     *
     * @return NodeElement
     */
    public function getTab($tab)
    {
        $groupSelector = $this->openGroupSelector();

        return $this->spin(function () use ($tab, $groupSelector) {
            return $groupSelector->find('css', sprintf('.AknDropdown-menuLink:contains("%s")', $tab));
        }, sprintf('Cannot find the tab named "%s"', $tab));
    }

    /**
     * Get the specified section
     *
     * @param string $title
     *
     * @return NodeElement
     */
    public function getSection($title)
    {
        return $this->find('css', sprintf('div.tabsection-title:contains("%s")', $title));
    }

    /**
     * @param string $name
     * {@inheritdoc}
     */
    public function findField($name)
    {
        return $this->spin(function () use ($name) {
            if ($tab = $this->find('css', $this->elements['Active tab']['css'])) {
                return $tab->findField($name);
            }

            return parent::findField($name);
        }, sprintf('Could not find field "%s"', $name));
    }

    /**
     * Find field container
     *
     * @param string $name
     *
     * @throws TimeoutException
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findFieldContainer($name)
    {
        $label = $this->spin(function () use ($name) {
            return $this->find('css', sprintf('label:contains("%s")', $name));
        }, sprintf('Label containing text "%s" not found', $name));

        return $this->getClosest($label, 'AknFieldContainer');
    }

    /**
     * Get validation errors
     *
     * @return array:string
     */
    public function getValidationErrors()
    {
        $tooltips = $this->findAll('css', $this->elements['Validation errors']['css']);
        $errors   = [];

        foreach ($tooltips as $tooltip) {
            $errors[] = $tooltip->getAttribute('data-original-title');
        }

        return $errors;
    }

    /**
     * Get tooltips messages
     *
     * @return string[]
     */
    public function getTooltipMessages()
    {
        $tooltips = $this->findAll('css', $this->elements['Tooltips']['css']);

        $messages = [];
        foreach ($tooltips as $tooltip) {
            $messages[] = $tooltip->getAttribute('data-original-title');
        }

        return $messages;
    }

    /**
     * Open the available attributes popin
     */
    public function openAvailableAttributesMenu()
    {
        $this->getElement('Available attributes button')->click();
    }

    /**
     * Add available attributes
     *
     * @param array $attributes
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $attributeAddSelectElement = $this->spin(function () {
            return $this->getElement('Available attributes');
        }, 'Cannot find the add attribute element');
        $attributeAddSelectElement->addOptions($attributes);
    }

    /**
     * Attach file to file field
     *
     * @param string $locator
     * @param string $path
     *
     * @throws ElementNotFoundException
     * @throws TimeoutException
     */
    public function attachFileToField($locator, $path)
    {
        $this->spin(function () use ($locator) {
            return $this->findFieldContainer($locator)->find('css', 'input[type="file"]');
        }, sprintf('Cannot find "%s" file field', $locator))->attachFile($path);
    }

    /**
     * Remove file from file field
     *
     * @param string $locator
     *
     * @throws ElementNotFoundException
     */
    public function removeFileFromField($locator)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException($this->getSession(), 'form field', 'id|name|label|value', $locator);
        }

        $checkbox = $field->getParent()->find('css', 'input[type="checkbox"]');

        if (null === $checkbox) {
            throw new ElementNotFoundException(
                $this->getSession(),
                'Remove checkbox',
                'associated file input',
                $locator
            );
        }

        $checkbox->check();
    }

    /**
     * This method allows to fill a compound field by passing the label in reversed order separated
     * with whitespaces.
     *
     * Example:
     * We have a field "$" embedded inside a "Price" field
     * We can call fillField('$ Price', 26) to set the "$" value of parent field "Price"
     *
     * @param string  $field
     * @param string  $value
     * @param Element $element
     */
    public function fillField($field, $value, Element $element = null)
    {
        $label     = $this->extractLabelElement($field, $element);
        $fieldType = $this->getFieldType($label);

        switch ($fieldType) {
            case 'multiSelect2':
                $this->fillMultiSelect2Field($label, $value);
                break;
            case 'simpleSelect2':
                $this->fillSelect2Field($label, $value);
                break;
            case 'metric':
                $this->fillMetricField($label, $value);
                break;
            case 'switch':
                $this->fillSwitchField($field, $value);
                break;
            case 'datepicker':
                $this->fillDateField($label, $value);
                break;
            case 'select':
                $this->fillSelectField($label, $value);
                break;
            case 'wysiwyg':
                $this->fillWysiwygField($label, $value);
                break;
            case 'text':
                $this->fillTextField($label, $value);
                break;
            case 'compound':
                $this->fillCompoundField($label, $value);
                break;
            default:
                parent::fillField($label->labelContent, $value);
                break;
        }
    }

    /**
     * @return array
     */
    public function getHistoryRows()
    {
        return $this->spin(function () {
            return $this->getElement('Updates grid')->findAll('css', 'tbody tr.entity-version');
        }, 'Cannot find the history rows.');
    }

    /**
     * @param string $attribute
     */
    public function expandAttribute($attribute)
    {
        $label = $this->spin(function () use ($attribute) {
            return $this->find('css', sprintf('label:contains("%s")', $attribute));
        }, sprintf('Cannot find attribute "%s" field', $attribute));

        $this->expand($label);
    }

    /**
     * @param string $label
     */
    public function expand($label)
    {
        if ($icon = $label->getParent()->find('css', '.icon-caret-right')) {
            $icon->click();
        }
    }

    /**
     * @param string $groupField
     * @param string $field
     *
     * @throws \InvalidArgumentException
     */
    public function findFieldInTabSection($groupField, $field)
    {
        $tabSection = $this->spin(function () use ($groupField) {
            return $this->find(
                'css',
                sprintf('.tabsection-title:contains("%s")', $groupField)
            );
        }, sprintf('Could not find tab section "%s"', $groupField));

        $accordionContent = $tabSection->getParent()->find('css', '.tabsection-content');

        $this->spin(function () use ($accordionContent, $field) {
            return $accordionContent->findField($field);
        }, sprintf('Could not find a "%s" field inside the %s accordion group', $field, $groupField));
    }

    /**
     * Fill field in a simple popin
     *
     * @param array $fields
     */
    public function fillPopinFields($fields)
    {
        foreach ($fields as $fieldCode => $value) {
            $field = $this->spin(function () use ($fieldCode) {
                $label = $this->find('css', sprintf('.modal label:contains("%s")', $fieldCode));
                if ($label === null) {
                    return false;
                }

                return $this->getClosest($label, 'AknFieldContainer')->find('css', 'input');
            }, sprintf('Cannot find "%s" in popin field', $fieldCode));

            $field->setValue($value);
        }
    }

    /**
     * Check if a select field contains (or not) the specified choices
     *
     * @param string $label
     * @param array  $choices
     * @param bool   $isExpected
     * @param bool   $strict
     *
     * @throws ExpectationException
     */
    public function checkFieldChoices($label, array $choices, $isExpected = true, $strict = false)
    {
        $select2 = $this->spin(function () use ($label) {
            $labelElement = $this->extractLabelElement($label);
            $container = $this->getClosest($labelElement, 'AknFieldContainer');
            if (null === $container) {
                return false;
            }

            return $container->find('css', '.select2-container');
        }, 'Impossible to find the select');
        $select2 = $this->decorate($select2, [Select2Decorator::class]);
        $selectChoices = $select2->getAvailableValues();

        if ($isExpected && true === $strict) {
            if ($selectChoices !== $choices) {
                throw new ExpectationException(sprintf(
                    'Expecting to see exactly %s, %s found',
                    json_encode($choices),
                    json_encode($selectChoices)
                ), $this->getSession());
            }
        } elseif ($isExpected) {
            foreach ($choices as $choice) {
                if (!in_array($choice, $selectChoices)) {
                    throw new ExpectationException(sprintf(
                        'Expecting to find choice "%s" in field "%s"',
                        $choice,
                        $label
                    ), $this->getSession());
                }
            }
        } else {
            foreach ($choices as $choice) {
                if (in_array($choice, $selectChoices)) {
                    throw new ExpectationException(sprintf(
                        'Choice "%s" should not be in available for field "%s"',
                        $choice,
                        $label
                    ), $this->getSession());
                }
            }
        }
    }

    /**
     * Returns the 'Add attributes' node element
     *
     * @throws SpinException
     *
     * @return NodeElement
     */
    public function getAttributeAddSelect()
    {
        return $this->spin(function () {
            return $this->getElement('Available attributes');
        }, 'Cannot find the add attribute element');
    }

    /**
     * Returns the 'Add attributes by group' node element
     *
     * @throws SpinException
     *
     * @return NodeElement
     */
    public function getAttributeGroupAddSelect()
    {
        return $this->spin(function () {
            return $this->getElement('Available attribute groups');
        }, 'Cannot find the add attribute element');
    }

    /**
     * Find a price field
     *
     * @param string $name
     * @param string $currency
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findPriceField($name, $currency)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label', 'value', $name);
        }

        $labels = $label->getParent()->findAll('css', '.currency-label');

        $fieldNum = null;
        foreach ($labels as $index => $element) {
            if ($element->getText() === $currency) {
                $fieldNum = $index;
                break;
            }
        }

        if ($fieldNum === null) {
            throw new ElementNotFoundException($this->getSession(), 'price field', 'id|name|label|value', $currency);
        }

        $fields = $label->getParent()->findAll('css', 'input[type="text"]');

        if (!isset($fields[$fieldNum])) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $name);
        }

        return $fields[$fieldNum];
    }

    /**
     * Extracts and return the label NodeElement, identified by $field content and $element
     *
     * @param string           $field
     * @param ElementInterface $element
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function extractLabelElement($field, ElementInterface $element = null)
    {
        $subLabelContent = null;
        $channel         = null;
        $labelContent    = $field;

        if (false !== strpbrk($field, '€$')) {
            if (false !== strpos($field, ' ')) {
                list($subLabelContent, $labelContent) = explode(' ', $field);
            }
        }

        if ($element) {
            $label = $this->spin(function () use ($element, $labelContent) {
                return $element->find('css', sprintf('label:contains("%s")', $labelContent));
            }, sprintf('Cannot find "%s" label', $labelContent));
        } else {
            $labelParts = explode(' ', $labelContent);
            $channel   = in_array(reset($labelParts), ['mobile', 'ecommerce', 'print', 'tablet']) ?
                reset($labelParts) :
                null;

            if (null !== $channel) {
                $labelContent = substr($labelContent, strlen($channel . ' '));
            }

            $label = $this->spin(function () use ($labelContent) {
                return $this->find('css', sprintf('label:contains("%s")', $labelContent));
            }, sprintf('Cannot find "%s" label', $labelContent));
        }

        if (!$label) {
            $label = new \stdClass();
        }

        $label->channel         = $channel;
        $label->labelContent    = $labelContent;
        $label->subLabelContent = $subLabelContent;

        return $label;
    }

    /**
     * Guesses the type of field identified by $label and returns it.
     *
     * Possible identified fields are :
     * [multiSelect2, simpleSelect2, datepicker, select, wysiwyg, text, compound]
     *
     * @param $label
     *
     * @return string
     */
    protected function getFieldType($label)
    {
        if (null === $label || !($label instanceof NodeElement)) {
            return null;
        }

        if (null !== $label->subLabelContent) {
            return 'compound';
        }

        if ($label->hasAttribute('for')) {
            $for = $label->getAttribute('for');
            $forElement = $this->getClosest($label, 'AknFieldContainer')->find('css', '#s2id_' . $for);

            if (0 === strpos($for, 's2id_') || null !== $forElement) {
                if ($this->getClosest($label, 'AknFieldContainer')->find('css', '.select2-container-multi')) {
                    return 'multiSelect2';
                } elseif ($this->getClosest($label, 'AknFieldContainer')->find('css', '.select2-container')) {
                    return 'simpleSelect2';
                }

                return 'select';
            }

            if ($this->getClosest($label, 'AknFieldContainer')->find('css', '.metric-container')) {
                return 'metric';
            }

            if ($this->getClosest($label, 'AknFieldContainer')->find('css', '.switch')) {
                return 'switch';
            }

            if (null !== $this->find('css', sprintf('#date_selector_%s', $for))) {
                return 'datepicker';
            }

            $field = $this->find('css', sprintf('#%s', $for));

            if (null !== $field && $field->getTagName() === 'select') {
                return 'select';
            }

            if (null !== $field && false !== strpos($field->getAttribute('class'), 'wysiwyg')) {
                return 'wysiwyg';
            }
        }

        return 'text';
    }

    /**
     * Fills a multivalues Select2 field with $value, identified by its $label.
     * It deletes existing selected values from field if not present in $value.
     *
     * $value can be a string of multiple values. Each value must be separated with comma, eg :
     * 'Hot, Dry, Fresh'
     *
     * @param NodeElement $label
     * @param string      $value
     *
     * @throws \InvalidArgumentException
     */
    protected function fillMultiSelect2Field(NodeElement $label, $value)
    {
        $field = $this->decorate(
            $this->getClosest($label, 'AknFieldContainer')->find('css', '.select2-container'),
            [Select2Decorator::class]
        );

        $field->setValue($value);
    }

    /**
     * Fills a simple (unique value) select2 field with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillSelect2Field(NodeElement $label, $value)
    {
        $container = $this->getClosest($label, 'AknFieldContainer');

        $select2Container = $this->spin(function () use ($container) {
            return $container->find('css', '.select2-container');
        }, 'Can not find the select2 container.');

        $field = $this->decorate(
            $select2Container,
            [Select2Decorator::class]
        );

        $field->setValue($value);

        return;
    }

    /**
     * Fills a metric element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     *
     * @throws \InvalidArgumentException
     */
    protected function fillMetricField(NodeElement $label, $value)
    {
        if (false !== strpos($value, ' ')) {
            list($text, $select) = explode(' ', $value);
        } else {
            $text   = $value;
            $select = null;
        }

        $fieldContainer = $this->getClosest($label, 'AknFieldContainer');
        $textField = $fieldContainer->find('css', 'input.amount');
        $textField->setValue($text);

        if (null !== $select) {
            $selectField = $fieldContainer->find('css', 'select.unit');
            $selectField->selectOption($select);
        }
    }

    /**
     * Fills a switch element with $value, identified by its $label.
     *
     * @param string $field
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function fillSwitchField($field, $value)
    {
        if ('Yes' === $value) {
            return $this->toggleSwitch($field, true);
        }

        if ('No' === $value) {
            return $this->toggleSwitch($field, false);
        }

        throw new \InvalidArgumentException(sprintf(
            'Switch fields accept only "Yes" or "No" value, %s provided.',
            $value
        ));
    }

    /**
     * Fills a select element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillSelectField(NodeElement $label, $value)
    {
        $field = $this->getClosest($label, 'AknFieldContainer')->find('css', 'select');

        $field->selectOption($value);
    }

    /**
     * Fills a Wysiwyg editor element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillWysiwygField(NodeElement $label, $value)
    {
        $for = $label->getAttribute('for');

        $this->getSession()->executeScript(
            sprintf("$('#%s').val('%s');", $for, $value)
        );
    }

    /**
     * Fills a date field element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillDateField(NodeElement $label, $value)
    {
        $for = $label->getAttribute('for');

        $this->getSession()->executeScript(
            sprintf("$('#%s').val('%s').trigger('change');", $for, $value)
        );
    }

    /**
     * Fills a text field element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillTextField(NodeElement $label, $value)
    {
        if (!$label->getAttribute('for') && null !== $label->channel) {
            $label = $label->getParent()->find('css', sprintf('[data-scope="%s"] label', $label->channel));
        }

        $for   = $label->getAttribute('for');
        $field = $this->spin(function () use ($for) {
            return $this->find('css', sprintf('#%s', $for));
        }, sprintf('Cannot find element field with id %s', $for));

        $this->spin(function () use ($field, $value) {
            $field->setValue($value);

            return $field->getValue() === $value;
        }, sprintf('Cannot fill field "%s" with value "%s"', $label->getHtml(), $value));

        $this->getSession()->executeScript(
            sprintf("$('#%s').trigger('change');", $for)
        );
    }

    /**
     * Fills a compound field with $value, by passing the $label in reversed order separated
     * with whitespaces.
     *
     * Example:
     * We have a field "$" embedded inside a "Price" field
     * We can call fillField('$ Price', 26) to set the "$" value of parent field "Price"
     *
     * @param NodeElement $label
     * @param string      $value
     *
     * @throws ElementNotFoundException
     */
    protected function fillCompoundField(NodeElement $label, $value)
    {
        if (!$label->subLabelContent) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "%s" field is compound but the sub label was not provided',
                    $label->labelContent
                )
            );
        }

        $this->expand($label);

        $field = $this->findPriceField($label->labelContent, $label->subLabelContent);
        $field->setValue($value);
    }

    /**
     * @param string $type
     *
     * @return NodeElement
     */
    public function openGroupSelector($type = 'Group')
    {
        $groupSelector = $this->spin(function () use ($type) {
            $groupSelectors = $this->findAll('css', $this->elements[sprintf('%s selector', $type)]['css']);
            foreach ($groupSelectors as $groupSelector) {
                if ($groupSelector->isVisible()) {
                    return $groupSelector;
                }
            }

            return null;
        }, sprintf('Can not find the "%s" selector', $type));

        $this->spin(function () use ($groupSelector) {
            if ($groupSelector->hasClass('open')) {
                return true;
            }

            $groupSelector->find('css', '[data-toggle="dropdown"]')->click();

            return false;
        }, sprintf('Can not open the "%s" selector', $type));

        return $groupSelector;
    }

    /**
     * Returns if the "add available attributes" button is enabled
     *
     * @return bool
     *
     * @throws TimeoutException
     */
    public function isAvailableAttributeEnabled()
    {
        $button = $this->spin(function () {
            return $this->find('css', $this->elements['Available attributes button']['css']);
        }, 'Cannot find available attribute button');

        return !$this->getClosest($button, 'select2-container')->hasClass('select2-container-disabled');
    }

    /**
     * Finds a select2 field identified by its label
     *
     * @param string $label
     * @return mixed|\Pim\Behat\Decorator\ElementDecorator
     * @throws TimeoutException
     */
    public function findSelect2Field($label)
    {
        $select2 = $this->spin(function () use ($label) {
            $labelElement = $this->extractLabelElement($label);
            $container = $this->getClosest($labelElement, 'AknFieldContainer');
            if (null === $container) {
                return false;
            }

            return $container->find('css', '.select2-container');
        }, 'Impossible to find the select2 field');
        $select2 = $this->decorate($select2, [Select2Decorator::class]);

        return $select2;
    }
}
