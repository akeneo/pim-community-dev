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
class ProductEdit extends Page
{
    protected $path = '/enrich/product/{id}/edit';

    protected $elements = array(
        'Locales dropdown'                => array('css' => '#locale-switcher'),
        'Available attributes'            => array('css' => '#attributes .ui-multiselect-checkboxes'),
        'Available attributes add button' => array('css' => 'a:contains("Add")'),
        'Available attributes menu'       => array('css' => 'button:contains("Add attributes")'),
        'Title'                           => array('css' => '.navbar-title'),
        'Tabs'                            => array('css' => '#form-navbar'),
        'Locales selector'                => array('css' => '#pim_product_locales'),
        'Enable switcher'                 => array('css' => '#pim_product_enabled'),
    );

    public function findLocaleLink($locale, $content = null)
    {
        $link = $this->getElement('Locales dropdown')->findLink($locale);

        if ($content) {
            if (strpos($link->getText(), $content) === false) {
                return null;
            }
        }

        return $link;
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
        $this->getElement('Locales dropdown')->clickLink($locale);
    }

    public function findLocale($locale, $label)
    {
        return $this->getElement('Locales dropdown')->find('css', sprintf(
            'a:contains("%s"):contains("%s")', strtoupper($locale), $label
        ));
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
            ->find('css', sprintf('li:contains("%s")', $attribute))
        ;
    }

    public function openAvailableAttributesMenu()
    {
        $this->getElement('Available attributes menu')->click();
    }

    public function selectAvailableAttribute($attribute)
    {
        $label = $this
            ->getElement('Available attributes')
            ->find('css', sprintf('li:contains("%s") label', $attribute))
        ;

        if (!$label) {
            throw new \Exception(sprintf('Could not find available attribute "%s".', $attribute));
        }

        $label->click();
    }

    public function addSelectedAvailableAttributes()
    {
        $this
            ->getElement('Available attributes add button')
            ->press()
        ;
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
        $titleElt = $this->getElement('Title');

        $subtitle        = $titleElt->find('css', '.sub-title');
        $separator       = $titleElt->find('css', '.separator');
        $name            = $titleElt->find('css', '.product-name');

        if (!$subtitle || !$separator || !$name ) {
            throw new \Exception('Could not find product title');
        }

        return sprintf(
            '%s%s%s',
            trim($subtitle->getText()),
            trim($separator->getText()),
            trim($name->getText())
        );
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }

    public function disableProduct()
    {
        $this->getElement('Enable switcher')->uncheck();

        return $this;
    }

    public function enableProduct()
    {
        $this->getElement('Enable switcher')->check();

        return $this;
    }
}
