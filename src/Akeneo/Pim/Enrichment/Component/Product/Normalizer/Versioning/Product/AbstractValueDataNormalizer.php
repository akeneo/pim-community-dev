<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueDataNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            $this->getFieldName($object, $context) => $this->doNormalize($object, $format, $context),
        ];
    }

    /**
     * Get the field name
     *
     * @param object $object  Object to normalize
     * @param array  $context Context options for the normalizer
     *
     * @throws \InvalidArgumentException when the context does not contain a "field_name" key
     *
     * @return string
     */
    protected function getFieldName($object, array $context = [])
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

    /**
     * Normalize a product value data
     *
     * @param object $object  object to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array|scalar
     */
    abstract protected function doNormalize($object, $format = null, array $context = []);
}
