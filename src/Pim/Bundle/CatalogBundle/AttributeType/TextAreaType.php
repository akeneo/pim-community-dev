<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\TextAreaType as FlexTextAreaType;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Text area attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaType extends FlexTextAreaType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormAlias(FlexibleValueInterface $value)
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
            ],
            'searchable' => [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ]
        ];

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_textarea';
    }
}
