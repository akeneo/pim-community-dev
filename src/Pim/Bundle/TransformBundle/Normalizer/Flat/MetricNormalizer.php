<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\MetricInterface;

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
        return $data instanceof MetricInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $context = $this->resolveContext($context);
        $decimalsAllowed = !array_key_exists('decimals_allowed', $context) || true === $context['decimals_allowed'];

        if ('multiple_fields' === $context['metric_format']) {
            $fieldKey = $this->getFieldName($object, $context);
            $unitFieldKey = sprintf('%s-unit', $fieldKey);

            $data = $this->getMetricData($object, false, $decimalsAllowed);
            $result = [
                $fieldKey     => $data,
                $unitFieldKey => '' === $data ? '' : $object->getUnit(),
            ];
        } else {
            $result = [
                $this->getFieldName($object, $context) => $this->getMetricData($object, true, $decimalsAllowed),
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
     * @param MetricInterface $metric
     * @param bool            $withUnit
     * @param bool            $decimalsAllowed
     *
     * @return string
     */
    public function getMetricData(MetricInterface $metric, $withUnit, $decimalsAllowed = true)
    {
        $data = $metric->getData();
        if (null === $data || '' === $data) {
            return '';
        }

        $pattern = $decimalsAllowed ? '%.4F' : '%d';
        if ($withUnit) {
            $data = sprintf($pattern. ' %s', $metric->getData(), $metric->getUnit());
        } else {
            $data = sprintf($pattern, $metric->getData());
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
