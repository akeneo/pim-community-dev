<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    const DECIMAL_PRECISION = 4;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        if ($entity->getData() instanceof Collection) {
            $data = [];
            foreach ($entity->getData() as $item) {
                $data[] = $this->serializer->normalize($item, $format, $context);
                sort($data);
            }
        } else {
            $data = $this->serializer->normalize($entity->getData(), $format, $context);

            // if decimals_allowed is false, we return an integer
            // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
            $attribute = $entity->getAttribute();
            if (AttributeTypes::NUMBER === $attribute->getAttributeType() && null !== $data && is_numeric($data)) {
                $data = $attribute->isDecimalsAllowed()
                    ? number_format($data, static::DECIMAL_PRECISION, '.', '') : (int) $data;
            }
        }

        return [
            'locale' => $entity->getLocale(),
            'scope'  => $entity->getScope(),
            'data'   => $data,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && 'standard' === $format;
    }
}
