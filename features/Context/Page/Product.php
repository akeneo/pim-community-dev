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
        'Locales dropdown' => array('css' => '.locales')
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

    public function getFieldValue($field)
    {
        if (null === $field = $this->findField($field)) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field ', 'id|name|label|value', $field
            );
        }

        return $field->getValue();
    }

    public function switchLocale($locale)
    {
        $this->getElement('Locales dropdown')->clickLink(ucfirst($locale));
    }

    public function getFieldAt(AttributeGroup $group, $position)
    {
        $locator = sprintf(
            '#tabs-%s label', $group->getId()
        );

        $fields = $this->findAll('css', $locator);

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
}
