<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Pre persist
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $product = $args->getObject();
        if ($product instanceof ProductInterface) {
            $dm = $args->getObjectManager();
            $this->convertMetricValues($dm, $product);
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
        $product = $args->getObject();
        if ($product instanceof ProductInterface) {
            $dm = $args->getObjectManager();
            $this->convertMetricValues($dm, $product);
        }
    }

    /**
     * Converts metric values
     *
     * @param DocumentManager  $dm
     * @param ProductInterface $product
     */
    protected function convertMetricValues(DocumentManager $dm, ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            $metric = $value->getData();
            if ($metric instanceof MetricInterface && $metric->getUnit()) {
                $this->createMetricBaseValues($metric);
                if (null !== $metric->getId()) {
                    $metadata = $dm->getClassMetadata(ClassUtils::getClass($metric));
                    $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($metadata, $metric);
                }
            }
        }
    }

    /**
     * Allow to create convert data in standard unit for metrics
     *
     * @param MetricInterface $metric
     */
    protected function createMetricBaseValues(MetricInterface $metric)
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
