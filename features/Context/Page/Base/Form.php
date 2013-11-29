<?php

namespace Context\Page\Base;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
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
                'Active tab'                      => array('css' => '.form-horizontal .tab-pane.active'),
                'Groups'                          => array('css' => '.tab-groups'),
                'Validation errors'               => array('css' => '.validation-tooltip'),
                'Available attributes form'       => array('css' => '#pim_available_product_attributes'),
                'Available attributes button'     => array('css' => 'button:contains("Add attributes")'),
                'Available attributes list'       => array('css' => '.pimmultiselect .ui-multiselect-checkboxes'),
                'Available attributes search'     => array('css' => '.pimmultiselect input[type="search"]'),
                'Available attributes add button' => array('css' => '.pimmultiselect a:contains("Add")'),
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
        $this->getElement('Tabs')->clickLink($tab);
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
                    $field = $label->getParent()->find('css', 'select');
                    $field->selectOption($value);
                } else {
                    $field = $this->find('css', sprintf('#%s', $for));
                    try {
                        $field->focus();
                    } catch (UnsupportedDriverActionException $e) {
                    }
                    $field->setValue($value);
                }
            } else {
                foreach (explode(',', $value) as $value) {
                    $field = $label->getParent()->find('css', 'select');
                    $field->selectOption(trim($value), true);
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

            $this->fillField($subLabelContent, $value, $label->getParent());
        }
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
}
