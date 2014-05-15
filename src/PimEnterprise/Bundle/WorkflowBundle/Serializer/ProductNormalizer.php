<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model;

/**
 * Product proposal normalizer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductNormalizer extends SerializerAwareNormalizer
    implements NormalizerInterface, DenormalizerInterface
{
    /** @staticvar string */
    const FORMAT = 'proposal';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [];
        foreach ($object->getValues() as $value) {
            $data[$this->createValueKey($value)] = $this->serializer->normalize($value, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        foreach ($data as $key => $proposal) {
            if (null === $value = $this->getValue($context['instance'], $key)) {
                throw new \Exception(sprintf('Cannot find value for "%s"', $key));
            }

            $this->serializer->denormalize($proposal, 'value', $format, ['instance' => $value]);
        }

        return $context['instance'];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Model\ProductInterface && self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::FORMAT === $format && 'product' === $type;
    }

    protected function getValue(Model\AbstractProduct $product, $key)
    {
        return $product->getValue(
            $this->getKeyPart($key, 'code'),
            $this->getKeyPart($key, 'locale'),
            $this->getKeyPart($key, 'scope')
        );
    }

    protected function createValueKey(Model\AbstractProductValue $value)
    {
        $attribute = $value->getAttribute();
        $key = $attribute->getCode();

        if ($attribute->isLocalizable()) {
            $key .= '-' . $value->getLocale();
        }

        if ($attribute->isScopable()) {
            $key .= '-' . $value->getScope();
        }

        return $key;
    }

    protected function getKeyPart($key, $part)
    {
        $parts = explode('-', $key);
        switch ($part) {
            case 'code':
                return $parts[0];

            case 'locale':
                return isset($parts[1]) ? $parts[1] : null;

            case 'scope':
                return isset($parts[2]) ? $parts[2] : null;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Unknown key part "%s"', $part)
                );
        }
    }
}
