<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

/**
 * Metric attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MetricType extends AbstractAttributeType
{
    protected $manager;

    /**
     * Constructor
     *
     * @param string         $backendType the backend type
     * @param string         $formType    the form type
     * @param MeasureManager $manager     The measure manager
     */
    public function __construct($backendType, $formType, MeasureManager $manager)
    {
        parent::__construct($backendType, $formType);

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
        return array(
            'label'        => $value->getAttribute()->getLabel(),
            'required'     => $value->getAttribute()->getRequired(),
            'units'        => $this->manager->getUnitSymbolsForFamily(
                $value->getAttribute()->getMetricFamily()
            ),
            'default_unit' => $value->getAttribute()->getDefaultMetricUnit(),
        );
    }
}
