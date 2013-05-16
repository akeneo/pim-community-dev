<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyEdit extends Page
{
    protected $path = '/{locale}/product/product-family/edit/{family_id}';

    protected $elements = array(
        'Available attributes' => array('css' => '#pim_available_product_attributes_attributes'),
        'Attributes'           => array('css' => '#attributes table'),
    );

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

    public function getAttribute($attribute, $group)
    {
        $groupNode = $this
            ->getElement('Attributes')
            ->find('css', sprintf(
                'tr.group:contains("%s")', $group
            ));

        if (!$groupNode) {
            throw new \RuntimeException(sprintf(
                'Couldn\'t find the attribute group "%s" in the attributes table',
                $group
            ));
        }

        return $groupNode
            ->getParent()
            ->find('css', sprintf(
                'td:contains("%s")', $attribute
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

    public function save()
    {
        $this->pressButton('Save');
    }

    public function getUrl(array $options)
    {
        $url = $this->getPath();

        foreach ($options as $parameter => $value) {
            $url = str_replace(sprintf('{%s}', $parameter), $value, $url);
        }

        return $url;
    }
}
