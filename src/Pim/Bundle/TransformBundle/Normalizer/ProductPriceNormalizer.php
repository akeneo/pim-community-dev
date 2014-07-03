<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductPrice;

/**
 * Normalize a product price into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPriceNormalizer implements NormalizerInterface
{
    /**
     * @var string[] $supportedFormats
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'data'     => $object->getData(),
            'currency' => $object->getCurrency()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractProductPrice && in_array($format, $this->supportedFormats);
    }
}
