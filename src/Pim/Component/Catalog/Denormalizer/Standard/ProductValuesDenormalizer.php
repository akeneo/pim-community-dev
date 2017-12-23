<?php

namespace Pim\Component\Catalog\Denormalizer\Standard;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalizer for a collection of product values
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesDenormalizer implements DenormalizerInterface
{
    /** @staticvar string */
    const PRODUCT_VALUES_TYPE = 'ProductValue[]';

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var string */
    protected $valueClass;

    /**
     * @param DenormalizerInterface        $denormalizer
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string                       $valueClass
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        AttributeRepositoryInterface $attributeRepository,
        $valueClass
    ) {
        $this->denormalizer = $denormalizer;
        $this->attributeRepository = $attributeRepository;
        $this->valueClass = $valueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $values = new ArrayCollection();

        foreach ($data as $attributeCode => $valuesData) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (!$attribute) {
                continue;
            }

            foreach ($valuesData as $valueData) {
                $value = $this->denormalizer->denormalize(
                    $valueData,
                    $this->valueClass,
                    'standard',
                    ['attribute' => $attribute] + $context
                );

                $values->add($value);
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === static::PRODUCT_VALUES_TYPE && 'standard' === $format;
    }
}
