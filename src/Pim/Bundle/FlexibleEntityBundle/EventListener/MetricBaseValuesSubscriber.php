<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;


use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;

/**
 * Metric base value listener
 *
 * Allow to create base data and unit from user values
 * These base values allow to compare each MetricValue
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricBaseValuesSubscriber implements EventSubscriber
{
    /**
     * @var MeasureConverter
     */
    protected $converter;

    /**
     * @var MeasureManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param MeasureConverter $converter
     */
    public function __construct(MeasureConverter $converter, MeasureManager $manager)
    {
        $this->converter = $converter;
        $this->manager   = $manager;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist', 'preUpdate'
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createMetricBaseValues($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->createMetricBaseValues($args);
    }

    protected function createMetricBaseValues($args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Metric) {
            $measureFamily = $this->manager->getFamilyFromUnitSymbol($entity->getUnit());
            $standardUnit = $this->manager->getStandardUnitSymbolForFamily($measureFamily);

            $this->converter->setFamily($measureFamily);
            $baseData = $this->converter->convert($entity->getUnit(), $standardUnit, $entity->getData());

            $entity
                ->setBaseData($baseData)
                ->setBaseUnit($standardUnit);
        }
    }
}
