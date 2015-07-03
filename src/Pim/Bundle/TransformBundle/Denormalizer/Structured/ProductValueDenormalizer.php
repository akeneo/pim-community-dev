<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product value denormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueDenormalizer implements SerializerAwareInterface, DenormalizerInterface
{
    /** @var string */
    protected $entityClass;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var string[] */
    protected $supportedFormats = ['json'];

    /**
     * @param string $entityClass
     */
    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!isset($context['attribute'])) {
            throw new InvalidArgumentException('Attribute must be passed in the context');
        }

        $attribute = $context['attribute'];

        if (!$attribute instanceof AttributeInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Attribute must be an instance of %s, %s given',
                    'Pim\Bundle\CatalogBundle\Model\AttributeInterface',
                    is_object($attribute) ? get_class($attribute) : gettype($attribute)
                )
            );
        }

        $data = $data + ['locale' => null, 'scope' => null, 'data' => null];

        $value = new $this->entityClass();
        $value->setAttribute($attribute);
        $value->setLocale($data['locale']);
        $value->setScope($data['scope']);

        $valueData = $this->serializer->denormalize($data['data'], $attribute->getAttributeType(), $format, $context);

        if (null !== $valueData) {
            $value->setData($valueData);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->entityClass && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
