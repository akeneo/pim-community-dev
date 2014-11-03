<?php

namespace Context\Page\Base;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Element\Element;

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
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            array(
                'Tabs'                            => array('css' => '#form-navbar'),
                'Oro tabs'                        => array('css' => '.navbar.scrollspy-nav'),
                'Active tab'                      => array('css' => '.form-horizontal .tab-pane.active'),
                'Groups'                          => array('css' => '.tab-groups'),
                'Validation errors'               => array('css' => '.validation-tooltip'),
                'Available attributes form'       => array('css' => '#pim_available_attributes'),
                'Available attributes button'     => array('css' => 'button:contains("Add attributes")'),
                'Available attributes list'       => array('css' => '.pimmultiselect .ui-multiselect-checkboxes'),
                'Available attributes search'     => array('css' => '.pimmultiselect input[type="search"]'),
                'Available attributes add button' => array('css' => '.pimmultiselect a.btn:contains("Add")'),
                'Updates grid'                    => array('css' => '.tab-pane.tab-history table.grid'),
            ),
            $this->elements
        );
    }

    /**
     * Press the save button
     */
    public function save()
    {
        $this->pressButton('Save');
    }

    /**
     * Visit the specified tab
     * @param string $tab
     */
    public function visitTab($tab)
    {
        $tabs = $this->find('css', $this->elements['Tabs']['css']);
        if (!$tabs) {
            $tabs = $this->getElement('Oro tabs');
        }
        $tabs->clickLink($tab);
    }

    /**
     * Get the tabs in the current page
     *
     * @return NodeElement[]
     */
    public function getTabs()
    {
        $tabs = $this->find('css', $this->elements['Tabs']['css']);
        if (!$tabs) {
            $tabs = $this->getElement('Oro tabs');
        }

        return $tabs->findAll('css', 'a');
    }

    /**
     * Visit the specified group
     * @param string $group
     */
    public function visitGroup($group)
    {
        $this->getElement('Groups')->clickLink($group);
    }

    /**
     * Get the specified section
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
        $errors = array();

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
     * @param array $attributes
     */
    public function addAvailableAttributes(array $attributes = array())
    {
        $this->openAvailableAttributesMenu();

        $search = $this->getElement('Available attributes search');
        foreach ($attributes as $attribute) {
            $search->setValue($attribute);
            if (!$search->isVisible()) {
                $this->openAvailableAttributesMenu();
            }
            $label = $this->getElement('Available attributes list')
                    ->find('css', sprintf('li:contains("%s") label', $attribute));

            if (!$label) {
                throw new \Exception(sprintf('Could not find available attribute "%s".', $attribute));
            }

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
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException($this->getSession(), 'form field', 'id|name|label|value', $locator);
        }

        if ($field->getAttribute('type') !== 'file') {
            $field = $field->getParent()->find('css', 'input[type="file"]');
        }

        $field->attachFile($path);
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
     * @param string  $labelContent
     * @param string  $value
     * @param Element $element
     *
     * @return null
     */
    public function fillField($labelContent, $value, Element $element = null)
    {
        $subLabelContent = null;
        if (false !== strpbrk($labelContent, '€$')) {
            if (false !== strpos($labelContent, ' ')) {
                list($subLabelContent, $labelContent) = explode(' ', $labelContent);
            }
        }

        if ($element) {
            $label = $element->find('css', sprintf('label:contains("%s")', $labelContent));
        } else {
            $label = $this->find('css', sprintf('label:contains("%s")', $labelContent));
        }

        if (null === $label) {
            return parent::fillField($labelContent, $value);
        }

        if ($label->hasAttribute('for')) {
            if (false === strpos($value, ',')) {
                $for = $label->getAttribute('for');
                if (0 === strpos($for, 's2id_')) {
                    // We are playing with a select2 widget
                    if (null !== $field = $label->getParent()->find('css', 'select')) {
                        return $field->selectOption($value);
                    }

                    // Maybe it's an ajax select2?
                    if (null !== $link = $label->getParent()->find('css', 'a.select2-choice')) {
                        $link->click();

                        // Wait for the ajax request to finish
                        $this->getSession()->wait(5000, '!$.active');

                        // Select the value in the displayed dropdown
                        if (null !== $item = $this->find('css', sprintf('#select2-drop li:contains("%s")', $value))) {
                            return $item->click();
                        }
                    }

                    throw new \InvalidArgumentException(
                        sprintf('Could not find select2 widget inside %s', $label->getParent()->getHtml())
                    );
                } elseif (preg_match('/_date$/', $for)) {
                    $this->getSession()->executeScript(
                        sprintf("$('#%s').val('%s').trigger('change');", $for, $value)
                    );
                } else {
                    $field = $this->find('css', sprintf('#%s', $for));
                    if ($field->getTagName() === 'select') {
                        $field->selectOption($value);
                    } else {
                        if (strpos($field->getAttribute('class'), 'wysiwyg') !== false) {
                            $this->getSession()->executeScript(
                                sprintf("$('#%s').val('%s');", $for, $value)
                            );
                        } else {
                            $field->setValue($value);
                        }
                    }
                }
            } else {
                foreach (explode(',', $value) as $value) {
                    $label->getParent()->find('css', 'input[type="text"]')->click();
                    $this->getSession()->wait(100000, "$('div:contains(\"Searching\")').length == 0");
                    $option = $this->find('css', sprintf('li:contains("%s")', trim($value)));
                    if (!$option) {
                        throw new \InvalidArgumentException(
                            sprintf('Could not find option "%s" for "%s"', trim($value), $label->getText())
                        );
                    }
                    $option->click();
                }
            }
        } else {
            if (!$subLabelContent) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The "%s" field is compound but the sub label was not provided',
                        $labelContent
                    )
                );
            }

            // it is a compound field, so let's expand the values
            $this->expand($label);

            $field = $this->findPriceField($labelContent, $subLabelContent);
            $field->setValue($value);
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
     *
     * @return null
     */
    public function expandAttribute($attribute)
    {
        if (null === $label = $this->find('css', sprintf('label:contains("%s")', $attribute))) {
            throw new \InvalidArgumentException(sprintf('Cannot find attribute "%s" field', $attribute));
        }

        return $this->expand($label);
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
     * Find a price field
     * @param string $name
     * @param string $currency
     *
     * @return NodeElement
     * @throws ElementNotFoundException
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
}
