<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;
use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

use Pim\Bundle\CatalogBundle\Model\Metric;

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
     * @param MeasureConverter $converter the measure converter
     * @param MeasureManager   $manager   the measure manager
     */
    public function __construct(MeasureConverter $converter, MeasureManager $manager)
    {
        $this->converter = $converter;
        $this->manager   = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist', 'preUpdate'
        );
    }

    /**
     * Pre persist
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createMetricBaseValues($args);
    }

    /**
     * Pre update
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->createMetricBaseValues($args);
    }

    /**
     * Allow to create convert data in standard unit for metrics
     *
     * @param LifecycleEventArgs $args
     */
    protected function createMetricBaseValues(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Metric) {
            $baseUnit = $this->manager->getStandardUnitForFamily($entity->getFamily());
            $baseData = $this->converter
                ->setFamily($entity->getFamily())
                ->convertBaseToStandard($entity->getUnit(), $entity->getData());

            $entity
                ->setBaseData($baseData)
                ->setBaseUnit($baseUnit);
        }
    }
}
