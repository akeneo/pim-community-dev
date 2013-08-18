<?php

namespace Context\Page\Product;

use Context\Page\Base\Form;
use Behat\Mink\Exception\ElementNotFoundException;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * Product edit page
 *
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    protected $path = '/enrich/product/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Locales dropdown' => array('css' => '#locale-switcher'),
                'Locales selector' => array('css' => '#pim_product_locales'),
                'Enable switcher'  => array('css' => '#pim_product_enabled'),
                'Updates grid'     => array('css' => '#history table.grid'),
                'Image preview'    => array('css' => '#lbImage'),
            )
        );
    }

    /**
     * @param string $locale
     * @param string $content
     *
     * @return NodeElement|null
     */
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

    /**
     * @param string $language
     */
    public function selectLanguage($language)
    {
        $this->getElement('Locales selector')->selectOption(ucfirst($language), true);
    }

    /**
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $this->getElement('Locales dropdown')->clickLink($locale);
    }

    /**
     * @param string $locale
     * @param string $label
     *
     * @return NodeElement
     */
    public function findLocale($locale, $label)
    {
        return $this->getElement('Locales dropdown')->find(
            'css',
            sprintf(
                'a:contains("%s"):contains("%s")',
                strtoupper($locale),
                $label
            )
        );
    }

    /**
     * @param string $group
     *
     * @return integer
     */
    public function getFieldsCountFor($group)
    {
        return count($this->getFieldsForGroup($group));
    }

    /**
     * @param string $group
     *
     * @return NodeElement
     */
    public function getFieldsForGroup($group)
    {
        $locator = sprintf('#tabs-%s label', $group instanceof AttributeGroup ? $group->getId() : 0);

        return $this->findAll('css', $locator);
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    public function findField($name)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $name);
        }

        $field = $label->getParent()->find('css', 'input');

        if (!$field) {
            throw new ElementNotFoundException($this->getSession(), 'form field ', 'id|name|label|value', $name);
        }

        return $field;
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        $controlGroupNode = $this->findField($field)->getParent()->getParent()->getParent()->getParent()->getParent();

        return $controlGroupNode->find('css', 'a.remove-attribute');
    }

    /**
     * Disable a product
     *
     * @return Edit
     */
    public function disableProduct()
    {
        $this->getElement('Enable switcher')->uncheck();

        return $this;
    }

    /**
     * Enable a product
     *
     * @return Edit
     */
    public function enableProduct()
    {
        $this->getElement('Enable switcher')->check();

        return $this;
    }

    /**
     * @return integer
     */
    public function countUpdates()
    {
        return count($this->getElement('Updates grid')->findAll('css', 'tbody tr'));
    }

    public function getImagePreview()
    {
        $preview = $this->getElement('Image preview');

        if (!$preview || false === strpos($preview->getAttribute('style'), 'display: block')) {
            return;
        }

        return $preview;
    }
}
