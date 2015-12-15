<?php

namespace Context\Page\Base;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

/**
 * Basic form page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Form extends Base
{
    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Tabs'                            => ['css' => '#form-navbar'],
                'Oro tabs'                        => ['css' => '.navbar.scrollspy-nav'],
                'Form tabs'                       => ['css' => '.nav-tabs.form-tabs'],
                'Associations list'               => ['css' => '#associations-list'],
                'Active tab'                      => ['css' => '.form-horizontal .tab-pane.active'],
                'Panel selector'                  => ['css' => '.panel-selector'],
                'Panel container'                 => ['css' => '.panel-container'],
                'Groups'                          => ['css' => '.tab-groups'],
                'Form Groups'                     => ['css' => '.attribute-group-selector'],
                'Validation errors'               => ['css' => '.validation-tooltip'],
                'Available attributes form'       => ['css' => '#pim_available_attributes'],
                'Available attributes button'     => ['css' => 'button:contains("Add attributes")'],
                'Available attributes list'       => ['css' => '.pimmultiselect .ui-multiselect-checkboxes'],
                'Available attributes search'     => ['css' => '.pimmultiselect input[type="search"]'],
                'Available attributes add button' => ['css' => '.pimmultiselect a.btn:contains("Add")'],
                'Updates grid'                    => ['css' => '.tab-pane.tab-history table.grid'],
            ],
            $this->elements
        );
    }

    /**
     * Press the save button
     */
    public function save()
    {
        $this->pressButton('Save');
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $this->getSession()->wait(10000, '!$.active');
        }
    }

    /**
     * Press the save button
     */
    public function saveAndClose()
    {
        $this->pressButton('Save and close');
    }

    /**
     * Visit the specified tab
     *
     * @param string $tab
     */
    public function visitTab($tab)
    {
        $tabs = $this->find('css', $this->elements['Tabs']['css']);
        if (!$tabs) {
            $tabs = $this->find('css', $this->elements['Oro tabs']['css']);
        }
        if (!$tabs) {
            $tabs = $this->find('css', $this->elements['Form tabs']['css']);
        }
        $tabs->clickLink($tab);
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
        });

        if (!$elt->find('css', sprintf('button.active:contains("%s")', $panel))) {
            $elt->find('css', sprintf('button[data-panel]:contains("%s")', $panel))->click();
        }
    }

    /**
     * Close the specified panel
     *
     * @param string $panel
     */
    public function closePanel()
    {
        $elt = $this->spin(function () {
            return $this->getElement('Panel container')->find('css', 'header .close');
        });

        $elt->click();
    }

    /**
     * Get the tabs in the current page
     *
     * @return NodeElement[]
     */
    public function getTabs()
    {
        $tabs = $this->spin(function () {
            return $this->find('css', $this->elements['Tabs']['css']);
        });

        if (!$tabs) {
            $tabs = $this->getElement('Oro tabs');
        }

        return $tabs->findAll('css', 'a');
    }

    /**
     * Get the form tab containg $tab text
     *
     * @param string $tab
     *
     * @return NodeElement|null
     */
    public function getFormTab($tab)
    {
        try {
            $node = $this->spin(function () use ($tab) {
                return $this->getElement('Form tabs')->find('css', sprintf('a:contains("%s")', $tab));
            }, 5);
        } catch (\Exception $e) {
            $node = null;
        }

        return $node;
    }

    /**
     * Get the specified tab
     *
     * @return NodeElement
     */
    public function getTab($tab)
    {
        return $this->find('css', sprintf('a:contains("%s")', $tab));
    }

    /**
     * Visit the specified group
     *
     * @param string $group
     *
     * @throws ElementNotFoundException
     * @throws \Exception
     */
    public function visitGroup($group)
    {
        $groups = $this->find('css', $this->elements['Groups']['css']);
        if (!$groups) {
            $groups = $this->getElement('Form Groups');

            $groupsContainer = $groups
                ->find('css', sprintf('.attribute-group-label:contains("%s")', $group));

            $button = null;

            if ($groupsContainer) {
                $button = $groupsContainer->getParent();
            }

            if (!$button) {
                throw new \Exception(sprintf('Could not find group "%s".', $group));
            }
            $button->click();
        } else {
            $groups->clickLink($group);
        }

        return true;
    }

    public function selectAssociation($assocation)
    {
        $associations = $this->spin(function () {
            return $this->find('css', $this->elements['Associations list']['css']);
        });

        $associations->clickLink($assocation);
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
        return $this->find('css', sprintf('div.accordion-heading:contains("%s")', $title));
    }

    /**
     * {@inheritdoc}
     */
    public function findField($name)
    {
        if ($tab = $this->find('css', $this->elements['Active tab']['css'])) {
            return $tab->findField($name);
        }

        return parent::findField($name);
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
        $this->openAvailableAttributesMenu();

        $search = $this->getElement('Available attributes search');
        foreach ($attributes as $attributeLabel) {
            $search->setValue($attributeLabel);
            $list  = $this->getElement('Available attributes list');
            $label = $this->spin(
                function () use ($list, $attributeLabel) {
                    return $list->find('css', sprintf('li label:contains("%s")', $attributeLabel));
                },
                20,
                sprintf('Could not find available attribute "%s".', $attributeLabel)
            );

            $label->click();
        }

        $this->getElement('Available attributes add button')->press();
    }

    /**
     * @param string $attribute
     * @param string $group
     *
     * @return NodeElement
     */
    public function findAvailableAttributeInGroup($attribute, $group)
    {
        return $this->getElement('Available attributes form')->find(
            'css',
            sprintf(
                'optgroup[label="%s"] option:contains("%s")',
                $group,
                $attribute
            )
        );
    }

    /**
     * Attach file to file field
     *
     * @param string $locator
     * @param string $path
     *
     * @throws ElementNotFoundException
     */
    public function attachFileToField($locator, $path)
    {
        $field = $this->spin(function () use ($locator) {
            $field = $this->findField($locator);
            if (null === $field) {
                return false;
            }
            if ($field->getAttribute('type') === 'file') {
                $field = $field->getParent()->find('css', 'input[type="file"]');
            }
            if ($field !== null) {
                return $field;
            }
            echo "retry find file input" . PHP_EOL;
        });

        $field->attachFile($path);
        $this->getSession()->executeScript('$(\'.edit .field-input input[type="file"]\').trigger(\'change\');');
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
        return $this->getElement('Updates grid')->findAll('css', 'tbody tr');
    }

    /**
     * @param string $attribute
     */
    public function expandAttribute($attribute)
    {
        if (null === $label = $this->find('css', sprintf('label:contains("%s")', $attribute))) {
            throw new \InvalidArgumentException(sprintf('Cannot find attribute "%s" field', $attribute));
        }

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
    public function findFieldInAccordion($groupField, $field)
    {
        $accordion = $this->find(
            'css',
            sprintf('.accordion-heading a:contains("%s")', $groupField)
        );

        if (!$accordion) {
            throw new \InvalidArgumentException(
                sprintf('Could not find accordion %s', $groupField)
            );
        }

        $accordionContent = $this->find('css', $accordion->getAttribute('href'));

        if (!$accordionContent->findField($field)) {
            throw new \InvalidArgumentException(
                sprintf('Could not find a "%s" field inside the %s accordion group', $field, $groupField)
            );
        }
    }

    /**
     * Fill field in a simple popin
     *
     * @param array $fields
     */
    public function fillPopinFields($fields)
    {
        foreach ($fields as $field => $value) {
            $field = $this->spin(function () use ($field) {
                return $this->find('css', sprintf('.modal-body .control-label:contains("%s") input', $field));
            });

            $field->setValue($value);
            $this->getSession()
                ->executeScript('$(\'.modal-body .control-label:contains("%s") input\').trigger(\'change\');');
        }
    }

    /**
     * Check if a select field contains (or not) the specified choices
     *
     * @param string $label
     * @param array  $choices
     * @param bool   $isExpected
     *
     * @throws ExpectationException
     */
    public function checkFieldChoices($label, array $choices, $isExpected = true)
    {
        $field = $this->spin(function () use ($label) {
            return $this->findField($label);
        });

        // TODO: Improve this part to make it work with regular selects if necessary
        $field->find('css', 'input[type="text"]')->click();
        $select2Drop   = $this->findById('select2-drop');
        $selectChoices = $this->spin(function () use ($select2Drop) {
            $choices = [];
            $select2Choices = $select2Drop->findAll('css', '.select2-result');
            if (!empty($select2Choices)) {
                foreach ($select2Choices as $select2Choice) {
                    $choices[] = trim($select2Choice->getText(), '[]');
                }

                return $choices;
            }
        }, 10);

        if ($isExpected) {
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
     * @param string  $field
     * @param Element $element
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function extractLabelElement($field, $element)
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
            });
        } else {
            $labeParts = explode(' ', $labelContent);
            $channel   = in_array(reset($labeParts), ['mobile', 'ecommerce', 'print', 'tablet']) ?
                reset($labeParts) :
                null;

            if (null !== $channel) {
                $labelContent = substr($labelContent, strlen($channel . ' '));
            }

            $label = $this->spin(function () use ($labelContent) {
                return $this->find('css', sprintf('label:contains("%s")', $labelContent));
            });
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

            if (0 === strpos($for, 's2id_')) {
                if ($label->getParent()->find('css', '.select2-container-multi')) {
                    return 'multiSelect2';
                } elseif ($label->getParent()->find('css', 'select')) {
                    return 'select';
                }

                return 'simpleSelect2';
            }

            if (null !== $this->find('css', sprintf('#date_selector_%s', $for))) {
                return 'datepicker';
            }

            $field = $this->find('css', sprintf('#%s', $for));

            if ($field->getTagName() === 'select') {
                return 'select';
            }

            if (false !== strpos($field->getAttribute('class'), 'wysiwyg')) {
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
        $allValues          = explode(',', $value);
        $selectedValues     = $label->getParent()->findAll('css', '.select2-search-choice');
        $selectedTextValues = array_map(function ($selectedValue) {
            return $selectedValue->getText();
        }, $selectedValues);

        // Delete tag from right to left to prevent select2 DOM change
        $selectedValues = array_reverse($selectedValues);

        foreach ($selectedValues as $selectedValue) {
            if (false === in_array($selectedValue->getText(), $allValues)) {
                $closeButton = $selectedValue->find('css', 'a');

                if (!$closeButton) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Could not find "%s" close button for "%s"',
                            trim($selectedValue->getText()),
                            $label->getText()
                        )
                    );
                }

                $closeButton->click();
            }
        }

        // Removing tags in MultiSelect2 drops an "animation" with opacity, we must
        // wait for it to completly vanish in order to reopen select list
        $this->getSession()->wait(2000);

        $allValues = array_filter($allValues);

        if (1 === count($allValues) && null !== $label->getParent()->find('css', 'select')) {
            $value = array_shift($allValues);
            $this->fillSelectField($label, $value);
        }

        // Fill in remaining values
        $remainingValues = array_diff($allValues, $selectedTextValues);

        foreach ($remainingValues as $value) {
            if (trim($value)) {
                $label->getParent()->find('css', 'input[type="text"]')->click();
                $this->getSession()->wait(100000, "$('div:contains(\"Searching\")').length == 0");
                $option = $this->find(
                    'css',
                    sprintf('.select2-result:not(.select2-selected) .select2-result-label:contains("%s")', trim($value))
                );

                if (!$option) {
                    throw new \InvalidArgumentException(
                        sprintf('Could not find option "%s" for "%s"', trim($value), $label->getText())
                    );
                }

                $option->click();
            }
        }
    }

    /**
     * Fills a simple (unique value) select2 field with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     *
     * @throws \InvalidArgumentException
     */
    protected function fillSelect2Field(NodeElement $label, $value)
    {
        if (trim($value)) {
            if (null !== $link = $label->getParent()->find('css', 'a.select2-choice')) {
                $link->click();

                $this->getSession()->wait(5000, '!$.active');

                $field = $this->spin(function () use ($value) {
                    return $this->find('css', sprintf('#select2-drop li:contains("%s")', $value));
                });

                $field->click();

                return;
            }

            throw new \InvalidArgumentException(
                sprintf('Could not find select2 widget inside %s', $label->getParent()->getHtml())
            );
        }
    }

    /**
     * Fills a select element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillSelectField(NodeElement$label, $value)
    {
        $field = $label->getParent()->find('css', 'select');

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
        $field = $this->find('css', sprintf('#%s', $for));

        $field->setValue($value);
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
        if (! $label->subLabelContent) {
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
}
