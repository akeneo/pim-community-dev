<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @var string[] $supportedFormats
     */
    protected $supportedFormats = array('csv', 'flat');

    /** @var integer */
    protected $precision;

    /**
     * @param integer $precision
     */
    public function __construct($precision = 4)
    {
        $this->precision = $precision;
    }

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
        $data = $entity->getData();
        $fieldName = $this->getFieldValue($entity);
        $result = null;

        if (is_array($data)) {
            $data = new ArrayCollection($data);
        }

        if (is_null($data)) {
            $result = [$fieldName => ''];
        } elseif (is_int($data)) {
            $result = [$fieldName => (string) $data];
        } elseif (is_float($data)) {
            $result = [$fieldName => sprintf(sprintf('%%.%sF', $this->precision), $data)];
        } elseif (is_string($data)) {
            $result = [$fieldName => $data];
        } elseif (is_bool($data)) {
            $result = [$fieldName => (string) (int) $data];
        } elseif (is_object($data)) {
            $context['field_name'] = $fieldName;
            $result = $this->serializer->normalize($data, $format, $context);
        }

        if (null === $result) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot normalize product value "%s" which data is a(n) "%s"',
                    $fieldName,
                    is_object($data) ? get_class($data) : gettype($data)
                )
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function getFieldValue($value)
    {
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        return $value->getAttribute()->getCode() . $suffix;
    }
}
