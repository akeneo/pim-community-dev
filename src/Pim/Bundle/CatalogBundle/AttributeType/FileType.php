<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * File attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'maxFileSize' => [
                'name'      => 'maxFileSize',
                'fieldType' => 'pim_number',
            ],
            'allowedExtensions' => [
                'name'    => 'allowedExtensions',
                'data'    => implode(',', $attribute->getAllowedExtensions()),
                'options' => [
                    'by_reference' => false,
                    'select2'      => true,
                    'attr'         => [
                        'class'     => 'AknTextField--select2',
                        'data-tags' => 'doc,docx,rtf,txt,csv,ppt,pptx,mp3,wav,svg,pdf'
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
        return AttributeTypes::FILE;
    }
}
