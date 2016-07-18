<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    const DECIMAL_PRECISION = 4;

    /**  @var string[] */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $object->getData();
        if (null !== $data && is_numeric($data) && isset($context['decimals_allowed'])) {
            $precision = true === $context['decimals_allowed'] ? static::DECIMAL_PRECISION : 0;
            $data = number_format($data, $precision, '.', '');
        }

        return [
            'data' => $data,
            'unit' => $object->getUnit() ?
                $object->getUnit() :
                $object->getValue()->getAttribute()->getDefaultMetricUnit(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && in_array($format, $this->supportedFormats);
    }
}
