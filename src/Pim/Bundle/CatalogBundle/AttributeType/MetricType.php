<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Metric attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricType extends AbstractAttributeType
{
    /** @var MeasureManager $manager */
    protected $manager;

    /** @var TranslatorInterface $translator */
    protected $translator;

    /**
     * Constructor
     *
     * @param string                     $backendType       the backend type
     * @param string                     $formType          the form type
     * @param ConstraintGuesserInterface $constraintGuesser the form type
     * @param MeasureManager             $manager           the measure manager
     * @param TranslatorInterface        $translator        the translator for units
     */
    public function __construct(
        $backendType,
        $formType,
        ConstraintGuesserInterface $constraintGuesser,
        MeasureManager $manager,
        TranslatorInterface $translator
    ) {
        parent::__construct($backendType, $formType, $constraintGuesser);

        $this->manager    = $manager;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValueFormOptions(ProductValueInterface $value)
    {
        $unitsList = $this->manager->getUnitSymbolsForFamily($value->getAttribute()->getMetricFamily());
        $unitNames = array_combine(array_keys($unitsList), array_keys($unitsList));
        $units = array_map(
            function ($unit) {
                return $this->translator->trans($unit, [], 'measures');
            },
            $unitNames
        );

        $options = array_merge(
            parent::prepareValueFormOptions($value),
            [
                'units'        => $units,
                'default_unit' => $value->getAttribute()->getDefaultMetricUnit(),
                'family'       => $value->getAttribute()->getMetricFamily()
            ]
        );
        $options['default_unit'] = [$options['default_unit']];

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'numberMin' => [
                'name'      => 'numberMin',
                'fieldType' => 'pim_number'
            ],
            'numberMax' => [
                'name'      => 'numberMax',
                'fieldType' => 'pim_number'
            ],
            'decimalsAllowed' => [
                'name'      => 'decimalsAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'negativeAllowed' => [
                'name'      => 'negativeAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'metricFamily' => [
                'name'    => 'metricFamily',
                'options' => [
                    'required'  => true,
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            'defaultMetricUnit' => [
                'name'    => 'defaultMetricUnit',
                'options' => [
                    'required' => true
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::METRIC;
    }
}
