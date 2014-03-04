<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\ImageType as FlexImageType;

/**
 * Image attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageType extends FlexImageType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'maxFileSize' => [
                'name'      => 'maxFileSize',
                'fieldType' => 'number',
                'options'   => [
                    'precision' => 2
                ]
            ],
            'allowedExtensions' => [
                'name'    => 'allowedExtensions',
                'data'    => implode(',', $attribute->getAllowedExtensions()),
                'options' => [
                    'by_reference' => false,
                    'select2'      => true,
                    'attr'         => [
                        'data-tags' => 'tif,tiff,gif,jpeg,jpg,jif,jfif,png,pdf,psd'
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_image';
    }
}
