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
    protected $path = '/{locale}/product/{id}/edit';

    protected $elements = array(
        'Locales dropdown'     => array('css' => '.locales'),
        'Available attributes' => array('css' => '#pim_available_product_attributes_attributes'),
    );

    protected $assertSession;

    public function setAssertSession($assertSession)
    {
        $this->assertSession = $assertSession;

        return $this;
    }

    public function assertLocaleIsDisplayed($locale)
    {
        $this->assertSession->elementTextContains('css', $this->elements['Locales dropdown']['css'], $locale);
    }

    public function selectLanguage($language)
    {
        $this->checkField($language);
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function setFieldValue($field, $value)
    {
        return $this->findField($field)->setValue($value);
    }

    public function getFieldValue($field)
    {
        return $this->findField($field)->getValue();
    }

    public function switchLocale($locale)
    {
        $this->getElement('Locales dropdown')->clickLink(ucfirst($locale));
    }

    public function getFieldAt($group, $position)
    {
        $fields  = $this->findAll('css', sprintf(
            '#tabs-%s label', $group instanceof AttributeGroup ? $group->getId() : 0
        ));

        if (0 === count($fields)) {
            throw new \Exception(sprintf(
                'Couldn\'t find elements that matches "%s"', $locator
            ));
        }

        if (!isset($fields[$position])) {
            throw new \Exception(sprintf(
                'Cannot found %dth field in group "%s"', $position + 1, $group->getName()
            ));
        }

        return $fields[$position];
    }

    public function findField($name)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form label ', 'value', $name
            );
        }

        $field = $label->getParent()->find('css', 'input[type="text"]');

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
}
