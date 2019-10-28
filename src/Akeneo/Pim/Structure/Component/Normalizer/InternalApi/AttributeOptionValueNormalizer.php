<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute option value normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id'     => $object->getId(),
            'locale' => $object->getLocale(),
            'value'  => $object->getValue()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionValueInterface && $format === 'array';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
