<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ElementNotFoundException;

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
        return $this->findField($language)->check();
    }

    public function save()
    {
        return $this->findButton('Save')->press();
    }

    public function getFieldValue($field)
    {
        return $this->findField($field)->getValue();
    }
}
