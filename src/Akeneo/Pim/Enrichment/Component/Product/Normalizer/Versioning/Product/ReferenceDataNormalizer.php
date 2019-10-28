<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a reference data into a string
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            $this->getFieldName($context) => $object->getCode(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Get the field name
     *
     * @param array $context Context options for the normalizer
     *
     * @throws \InvalidArgumentException when the context does not contain a "field_name" key
     *
     * @return string
     */
    protected function getFieldName(array $context = [])
    {
        if (!array_key_exists('field_name', $context)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Missing required "field_name" context value, got "%s"',
                    implode(', ', array_keys($context))
                )
            );
        }

        return $context['field_name'];
    }
}
