<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType as FormTextType;

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
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'maxCharacters' => [
                'name'      => 'maxCharacters',
                'fieldType' => FormTextType::class
            ],
            'wysiwygEnabled' => [
                'name'      => 'wysiwygEnabled',
                'fieldType' => SwitchType::class
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::TEXTAREA;
    }
}
