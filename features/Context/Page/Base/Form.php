<?php

namespace Context\Page\Base;

use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Basic form page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
            $this->elements,
            array(
                'Tabs'                            => array('css' => '#form-navbar'),
                'Active tab'                      => array('css' => '.form-horizontal .tab-pane.active'),
                'Groups'                          => array('css' => '.tab-groups'),
                'Validation errors'               => array('css' => '.validation-tooltip'),
                'Available attributes form'       => array('css' => '#pim_available_product_attributes'),
                'Available attributes button'     => array('css' => 'button:contains("Add attributes")'),
                'Available attributes list'       => array('css' => '.pimmultiselect .ui-multiselect-checkboxes'),
                'Available attributes add button' => array('css' => '.pimmultiselect a:contains("Add")'),
            )
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

        foreach ($attributes as $attribute) {
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
}
