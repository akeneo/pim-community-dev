<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Text area attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function prepareValueFormAlias(ProductValueInterface $value)
    {
        if ($value->getAttribute()->isWysiwygEnabled()) {
            return 'pim_wysiwyg';
        }

        return parent::prepareValueFormAlias($value);
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'defaultValue' => [
                'name'      => 'defaultValue',
                'fieldType' => 'textarea'
            ],
            'maxCharacters' => [
                'name'      => 'maxCharacters',
                'fieldType' => 'integer'
            ],
            'wysiwygEnabled' => [
                'name'      => 'wysiwygEnabled',
                'fieldType' => 'switch'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_textarea';
    }
}
