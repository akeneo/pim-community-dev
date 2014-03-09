<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\FileType as FlexFileType;

/**
 * File attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileType extends FlexFileType
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
        return 'pim_catalog_file';
    }
}
