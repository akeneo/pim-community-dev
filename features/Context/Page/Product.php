<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ElementNotFoundException;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product extends Page
{
    protected $path = '/enrich/product/{id}/edit';

    protected $elements = array(
        'Locales dropdown'     => array('css' => '#locale-switcher'),
        'Available attributes' => array('css' => '#pim_available_product_attributes_attributes'),
        'Title'                => array('css' => '.product-title'),
        'Tabs'                 => array('css' => '#form-navbar'),
        'Locales selector'     => array('css' => '#pim_product_locales'),
    );

    public function findLocaleLink($locale)
    {
        return $this->getElement('Locales dropdown')->findLink(strtolower($locale));
    }

    public function selectLanguage($language)
    {
        $this->getElement('Locales selector')->selectOption(ucfirst($language), true);
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function switchLocale($locale)
    {
        $this->getElement('Locales dropdown')->clickLink(strtolower($locale));
    }

    public function getFieldAt($group, $position)
    {
        $fields = $this->getFieldsForGroup($group);

        if (0 === count($fields)) {
            throw new \Exception(sprintf(
                'Couldn\'t find group "%s"', $group
            ));
        }

        if (!isset($fields[$position])) {
            throw new \Exception(sprintf(
                'Couldn\'t find %dth field in group "%s"', $position + 1, $group
            ));
        }

        return $fields[$position];
    }

    public function getFieldsCountFor($group)
    {
        return count($this->getFieldsForGroup($group));
    }

    private function getFieldsForGroup($group)
    {
        $locator = sprintf(
            '#tabs-%s label', $group instanceof AttributeGroup ? $group->getId() : 0
        );

        return $this->findAll('css', $locator);
    }

    public function findField($name)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form label ', 'value', $name
            );
        }

        $field = $label->getParent()->find('css', 'input');

        if (!$field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field ', 'id|name|label|value', $name
            );
        }

        return $field;
    }

    public function getAvailableAttribute($attribute, $group)
    {
        return $this
            ->getElement('Available attributes')
            ->find('css', sprintf(
                'optgroup[label="%s"] option:contains("%s")',
                $group, $attribute
            ))
        ;
    }

    public function selectAvailableAttribute($attribute)
    {
        $this->getElement('Available attributes')->selectOption($attribute, true);
    }

    public function addSelectedAvailableAttributes()
    {
        $this->pressButton('Add attributes');
    }

    public function getRemoveLinkFor($field)
    {
        $controlGroupNode = $this
            ->findField($field)
            ->getParent()
            ->getParent()
            ->getParent()
            ->getParent()
            ->getParent()
        ;

        return $controlGroupNode->find('css', 'a.remove-attribute');
    }

    public function getTitle()
    {
        return str_replace(' ', '', $this->getElement('Title')->getText());
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }
}
