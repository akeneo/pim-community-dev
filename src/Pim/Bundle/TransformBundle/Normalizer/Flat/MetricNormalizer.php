<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\AbstractMetric;

/**
 * Normalize a metric data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer extends AbstractProductValueDataNormalizer
{
    /** @var array */
    protected $supportedFormats = array('csv', 'flat');

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractMetric && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $context = $this->resolveContext($context);

        if ('multiple_fields' === $context['metric_format']) {
            $fieldKey = $this->getFieldName($object, $context);
            $unitFieldKey = sprintf('%s-unit', $fieldKey);

            $data = $this->getMetricData($object, false);
            $result = [
                $fieldKey     => $data,
                $unitFieldKey => '' === $data ? '' : $object->getUnit(),
            ];
        } else {
            $result = [
                $this->getFieldName($object, $context) => $this->getMetricData($object, true),
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function doNormalize($object, $format = null, array $context = array())
    {
    }

    /**
     * Get the data stored in the metric
     *
     * @param AbstractMetric $metric
     * @param boolean        $withUnit
     *
     * @return string
     */
    public function getMetricData(AbstractMetric $metric, $withUnit)
    {
        $data = $metric->getData();
        if (null === $data || '' === $data) {
            return '';
        }

        if ($withUnit) {
            $data = sprintf('%.4F %s', $metric->getData(), $metric->getUnit());
        } else {
            $data = sprintf('%.4F', $metric->getData());
        }

        return $data;
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context = [])
    {
        $context = array_merge(['metric_format' => 'multiple_fields'], $context);

        if (!in_array($context['metric_format'], ['single_field', 'multiple_fields'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Value "%s" of "metric_format" context value is not allowed ' .
                    '(allowed values: "single_field, multiple_fields"',
                    $context['metric_format']
                )
            );
        }

        return $context;
    }
}
