<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCreation extends Page
{
    protected $path = '/enrich/product-attribute/create';

    protected $elements = array(
        'Attribute type selector' => array('css' => '#pim_product_attribute_form_attributeType'),
        'Attribute options'       => array('css' => 'table.sortable_options tbody tr'),
    );

    public function selectAttributeType($type)
    {
        $this
            ->getElement('Attribute type selector')
            ->selectOption($type)
        ;
    }

    public function getSection($title)
    {
        return $this->find('css', sprintf('div.accordion-heading:contains("%s")', $title));
    }

    public function countOptions()
    {
        return count($this->findAll('css', $this->elements['Attribute options']['css']));
    }
}
