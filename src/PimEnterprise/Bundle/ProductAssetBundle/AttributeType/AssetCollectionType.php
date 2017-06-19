<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\AttributeType;

use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Asset collection type
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetCollectionType extends AbstractAttributeType
{
    /** @var ConfigurationRegistryInterface */
    protected $referenceRegistry;

    /**
     * @param string                         $backendType       the backend type
     * @param string                         $formType          the form type
     * @param ConstraintGuesserInterface     $constraintGuesser the form type
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(
        $backendType,
        $formType,
        ConstraintGuesserInterface $constraintGuesser,
        ConfigurationRegistryInterface $registry
    ) {
        parent::__construct($backendType, $formType, $constraintGuesser);

        $this->referenceRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValueFormName(ProductValueInterface $value)
    {
        $referenceDataConf = $this->referenceRegistry->get($value->getAttribute()->getReferenceDataName());

        return $referenceDataConf->getName();
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        $attributes = parent::defineCustomAttributeProperties($attribute);

        unset(
            $attributes['availableLocales'],
            $attributes['unique'],
            $attributes['localizable'],
            $attributes['scopable']
        );

        return $attributes + [
            'reference_data_name' => [
                'name'      => 'reference_data_name',
                'fieldType' => HiddenType::class,
                'options'   => [
                    'data' => 'assets',
                ],
            ],
            'scopable' => [
                'name'      => 'scopable',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'localizable' => [
                'name'      => 'localizable',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::ASSETS_COLLECTION;
    }
}
