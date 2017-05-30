<?php

namespace Pim\Component\Catalog\Denormalizer\Standard;

use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
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

    /** @var ProductValueFactory */
    protected $productValueFactory;

    /**
     * @param ProductValueFactory $productValueFactory
     * @param string              $entityClass
     */
    public function __construct(ProductValueFactory $productValueFactory, $entityClass)
    {
        $this->productValueFactory = $productValueFactory;
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
                    'Pim\Component\Catalog\Model\AttributeInterface',
                    is_object($attribute) ? get_class($attribute) : gettype($attribute)
                )
            );
        }

        $data = $data + ['locale' => null, 'scope' => null, 'data' => null];

        $productValue = $this->productValueFactory->create($attribute, $data['scope'], $data['locale']);

        $valueData = $this->serializer->denormalize($data['data'], $attribute->getType(), $format, $context);

        if (null !== $valueData) {
            $productValue->setData($valueData);
        }

        return $productValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->entityClass && 'standard' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
