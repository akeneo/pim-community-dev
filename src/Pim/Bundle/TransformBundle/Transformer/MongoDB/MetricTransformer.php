<?php

namespace Pim\Bundle\TransformBundle\Transformer\MongoDB;

use Pim\Bundle\TransformBundle\Transformer\ObjectTransformerInterface;
use Pim\Bundle\CatalogBundle\Model\Metric;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;

use MongoId;

/**
 * Transform a metric entity into an MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformer implements ObjectTransformerInterface
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
    public function transform($metric, array $context = [])
    {
        $doc = new \StdClass();
        $doc->_id = new MongoId;
        $doc->unit = $object->getUnit();
        $doc->data = $object->getData();

        $this->createMetricBaseValues($object);

        $doc->baseUnit = $object->getBaseUnit();
        $doc->baseData = $object->getBaseData();

        $doc->family = $object->getFamily();

        return $doc;
    }

    /**
     * Allow to create convert data in standard unit for metrics
     *
     * @param Metric
     */
    protected function createMetricBaseValues(Metric $metric)
    {
        $baseUnit = $this->manager->getStandardUnitForFamily($metric->getFamily());
        if (is_numeric($metric->getData())) {
            $baseData = $this->converter
                ->setFamily($metric->getFamily())
                ->convertBaseToStandard($metric->getUnit(), $metric->getData());
        } else {
            $baseData = null;
        }

        $metric->setBaseData($baseData);
        $metric->setBaseUnit($baseUnit);
    }

}
