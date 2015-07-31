<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Multi options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionMultiSelectType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function prepareValueFormOptions(ProductValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $options['class']                = 'PimCatalogBundle:AttributeOption';
        $options['collection_id']        = $attribute->getId();
        $options['multiple']             = true;
        $options['minimum_input_length'] = $attribute->getMinimumInputLength();

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValueFormData(ProductValueInterface $value)
    {
        $iterator = $value->getData()->getIterator();

        if (true === $value->getAttribute()->getProperty('autoOptionSorting')) {
            $iterator->uasort('strcasecmp');
        } else {
            $iterator->uasort(
                function ($first, $second) {
                    return $first->getSortOrder() < $second->getSortOrder() ? -1 : 1;
                }
            );
        }

        return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'minimumInputLength' => [
                'name'      => 'minimumInputLength',
                'fieldType' => 'pim_number'
            ],
            'options' => [
                'name'      => 'options',
                'fieldType' => 'pim_enrich_options'
            ],
            'autoOptionSorting' => [
                'name'      => 'autoOptionSorting',
                'fieldType' => 'switch',
                'options'   => [
                    'label'         => 'Automatic option sorting',
                    'property_path' => 'properties[autoOptionSorting]',
                    'help'          => 'info.attribute.auto option sorting',
                    'attr'          => [
                        'class' => 'hide'
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
        return AttributeTypes::OPTION_MULTI_SELECT;
    }
}
