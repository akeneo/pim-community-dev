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
                'Active tab'                      => array('css' => '.tab-pane.active'),
                'Groups'                          => array('css' => '.tab-groups'),
                'Available attributes form'       => array('css' => '#pim_available_product_attributes'),
                'Available attributes button'     => array('css' => 'button:contains("Add attributes")'),
                'Available attributes list'       => array('css' => '#attribute-buttons .ui-multiselect-checkboxes'),
                'Available attributes add button' => array('css' => '#attribute-buttons a:contains("Add")'),
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
        return $this->getElement('Active tab')->findField($name);
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
}
