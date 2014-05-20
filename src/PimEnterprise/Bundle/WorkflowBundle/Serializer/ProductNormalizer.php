<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use PimEnterprise\Bundle\WorkflowBundle\Util\ProductValueKeyGenerator;

/**
 * Product proposal normalizer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @staticvar string */
    const FORMAT = 'proposal';

    protected $builder;

    protected $repository;

    /** @var ProductValueKeyGenerator */
    protected $keyGen;

    /**
     * @param ProductValueKeyGenerator|null $keyGen
     */
    public function __construct(
        ProductBuilder $builder,
        AttributeRepository $repository,
        ProductValueKeyGenerator $keyGen = null
    ) {
        $this->builder = $builder;
        $this->repository = $repository;
        $this->keyGen = $keyGen ?: new ProductValueKeyGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [];
        foreach ($object->getValues() as $value) {
            $data[$this->keyGen->generate($value)] = $this->serializer->normalize($value, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        // TODO (2014-05-16 00:10 by Gildas): $context['instance'] must be an AbstractProduct instance
        foreach ($data as $key => $proposal) {
            if (null === $value = $this->getValue($context['instance'], $key)) {
                $value = $this->createValue($context['instance'], $key);
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

    /**
     * Get the product value corresponding to the key
     *
     * @param Model\AbstractProduct $product
     *
     * @return Model\AbstractProductValue
     */
    protected function getValue(Model\AbstractProduct $product, $key)
    {
        return $product->getValue(
            $this->keyGen->getPart($key, ProductValueKeyGenerator::CODE),
            $this->keyGen->getPart($key, ProductValueKeyGenerator::LOCALE),
            $this->keyGen->getPart($key, ProductValueKeyGenerator::SCOPE)
        );
    }

    protected function createValue(Model\AbstractProduct $product, $key)
    {
        return $this
            ->builder
            ->addProductValue(
                $product,
                $this->repository->findOneBy(['code' => $this->keyGen->getPart($key, ProductValueKeyGenerator::CODE)]),
                $this->keyGen->getPart($key, ProductValueKeyGenerator::LOCALE),
                $this->keyGen->getPart($key, ProductValueKeyGenerator::SCOPE)
            );
    }
}
