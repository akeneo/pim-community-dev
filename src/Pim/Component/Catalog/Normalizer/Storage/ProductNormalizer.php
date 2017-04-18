<?php

namespace Pim\Component\Catalog\Normalizer\Storage;

use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transform a product object to a standardized array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    const FIELD_ASSOCIATIONS = 'associations';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /** @var NormalizerInterface */
    private $associationsNormalizer;

    /**
     * @param NormalizerInterface $propertiesNormalizer
     * @param NormalizerInterface $associationsNormalizer
     */
    public function __construct(NormalizerInterface $propertiesNormalizer, NormalizerInterface $associationsNormalizer)
    {
        $this->propertiesNormalizer   = $propertiesNormalizer;
        $this->associationsNormalizer = $associationsNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($product, $format, $context);
        $data[self::FIELD_ASSOCIATIONS] = $this->associationsNormalizer->normalize($product, $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'storage' === $format;
    }
}
