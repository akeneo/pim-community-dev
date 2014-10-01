<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Metric attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformer extends DefaultTransformer implements EntityUpdaterInterface
{
    /** @var MetricFactory */
    protected $metricFactory;

    /**
     * @param MetricFactory $metricFactory
     */
    public function __construct(MetricFactory $metricFactory)
    {
        $this->metricFactory = $metricFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        $suffixes = $columnInfo->getSuffixes();
        $suffix = array_pop($suffixes);

        if (!$object->getMetric()) {
            $metric = $this->metricFactory->createMetric($columnInfo->getAttribute()->getMetricFamily());
            $object->setMetric($metric);
        }

        if ('unit' === $suffix) {
            $object->getMetric()->setUnit($data);
        } else {
            $parts = preg_split('/\s+/', $data);
            $object->getMetric()->setData($parts[0] === '' ? null : $parts[0]);
            if (isset($parts[1])) {
                $object->getMetric()->setUnit($parts[1]);
            }
        }
    }
}
