<?php

namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;

/**
 * Metric attribute type
 */
class MetricType extends AbstractAttributeType
{
    protected $manager;

    /**
     * Constructor
     *
     * @param string                     $backendType       the backend type
     * @param string                     $formType          the form type
     * @param ConstraintGuesserInterface $constraintGuesser the form type
     * @param MeasureManager             $manager           The measure manager
     */
    public function __construct(
        $backendType,
        $formType,
        ConstraintGuesserInterface $constraintGuesser,
        MeasureManager $manager
    ) {
        parent::__construct($backendType, $formType, $constraintGuesser);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_metric';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        return array_merge(
            parent::prepareValueFormOptions($value),
            array(
                'units'        => $this->manager->getUnitSymbolsForFamily(
                    $value->getAttribute()->getMetricFamily()
                ),
                'default_unit' => $value->getAttribute()->getDefaultMetricUnit(),
            )
        );
    }
}
