<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Boolean attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'defaultValue' => [
                'name'      => 'defaultValue',
                'fieldType' => 'switch'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_boolean';
    }
}
