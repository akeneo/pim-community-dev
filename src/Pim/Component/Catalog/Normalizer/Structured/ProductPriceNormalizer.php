<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPriceNormalizer implements NormalizerInterface
{
    const DECIMAL_PRECISION = 2;

    /** @var string[] */
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
            'data'     => $data,
            'currency' => $object->getCurrency(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPriceInterface && in_array($format, $this->supportedFormats);
    }
}
