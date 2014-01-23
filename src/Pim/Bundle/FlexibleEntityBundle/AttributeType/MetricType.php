<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;

/**
 * Metric attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricType extends AbstractAttributeType
{
    /**
     * @var MeasureManager $manager
     */
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
        return 'pim_flexibleentity_metric';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        return array_merge(
            parent::prepareValueFormOptions($value),
            [
                'units'        => $this->manager->getUnitSymbolsForFamily(
                    $value->getAttribute()->getMetricFamily()
                ),
                'default_unit' => $value->getAttribute()->getDefaultMetricUnit(),
                'family'       => $value->getAttribute()->getMetricFamily()
            ]
        );
    }
}
