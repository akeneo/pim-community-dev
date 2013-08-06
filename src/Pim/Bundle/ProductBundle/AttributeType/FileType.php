<?php

namespace Pim\Bundle\ProductBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\FileType as OroFileType;

/**
 * File attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FileType extends OroFileType
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
                'name'    => 'allowedFileExtensions',
                'data'    => implode(',', $attribute->getAllowedFileExtensions()),
                'options' => array(
                    'by_reference' => false,
                    'attr'         => array(
                        'class' => 'multiselect'
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
        return 'pim_product_file';
    }
}
