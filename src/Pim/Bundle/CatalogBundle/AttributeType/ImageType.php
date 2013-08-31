<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\ImageType as OroImageType;

/**
 * Image attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageType extends OroImageType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = array(
            array(
                'name'      => 'allowedFileSources',
                'fieldType' => 'choice',
                'options'   => array(
                    'required' => true,
                    'choices'  => array(
                        'upload'   => 'Upload',
                        'external' => 'External'
                    )
                )
            ),
            array(
                'name'      => 'maxFileSize',
                'fieldType' => 'integer'
            ),
            array(
                'name'    => 'allowedExtensions',
                'data'    => implode(',', $attribute->getAllowedExtensions()),
                'options' => array(
                    'by_reference' => false,
                    'attr'         => array(
                        'class'     => 'multiselect',
                        'data-tags' => 'tif,tiff,gif,jpeg,jpg,jif,jfif,png,pdf,psd'
                    )
                )
            ),
            array(
                'name'      => 'translatable',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'availableLocales',
                'fieldType' => 'pim_product_available_locales'
            ),
            array(
                'name'      => 'scopable',
                'fieldType' => 'pim_product_scopable',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'disabled'  => true,
                    'read_only' => true
                )
            )
        );

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_image';
    }
}
