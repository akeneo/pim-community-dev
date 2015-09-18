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
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

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
    public function prepareValueFormOptions(ProductValueInterface $value)
    {
        $referenceDataConf   = $this->referenceRegistry->get($value->getAttribute()->getReferenceDataName());
        $options             = parent::prepareValueFormOptions($value);
        $options['class']    = $referenceDataConf->getClass();
        $options['multiple'] = true;

        return $options;
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
                'fieldType' => 'hidden',
                'options'   => [
                    'data' => 'assets',
                ],
            ],
            'scopable' => [
                'name'      => 'scopable',
                'fieldType' => 'pim_enrich_scopable',
                'options'   => [
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'localizable' => [
                'name'      => 'localizable',
                'fieldType' => 'switch',
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
        return 'pim_assets_collection';
    }
}
