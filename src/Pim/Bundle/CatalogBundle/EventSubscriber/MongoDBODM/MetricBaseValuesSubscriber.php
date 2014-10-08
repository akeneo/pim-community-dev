<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Model\AbstractMetric;

/**
 * Metric base value listener
 *
 * Allow to create base data and unit from user values
 * These base values allow to compare each Metric
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
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof AbstractMetric && $object->getUnit()) {
            $this->createMetricBaseValues($object);
        }
    }

    /**
     * Pre update
     * PreUpdate event needs to recompute change set
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof AbstractMetric && $object->getUnit()) {
            $this->createMetricBaseValues($object);

            $class = new ClassMetadata($object);
            $args->getObjectManager()->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $object);
        }
    }

    /**
     * Allow to create convert data in standard unit for metrics
     *
     * @param AbstractMetric $metric
     */
    protected function createMetricBaseValues(AbstractMetric $metric)
    {
        $baseUnit = $this->manager->getStandardUnitForFamily($metric->getFamily());
        if (is_numeric($metric->getData())) {
            $baseData = $this->converter
                ->setFamily($metric->getFamily())
                ->convertBaseToStandard($metric->getUnit(), $metric->getData());
        } else {
            $baseData = null;
        }

        $metric
            ->setBaseData($baseData)
            ->setBaseUnit($baseUnit);
    }
}
