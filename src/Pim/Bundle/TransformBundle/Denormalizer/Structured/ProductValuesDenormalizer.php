<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
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

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $valueClass;

    /** @var string[] */
    protected $supportedFormats = ['json'];

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
        $this->denormalizer        = $denormalizer;
        $this->attributeRepository = $attributeRepository;
        $this->valueClass          = $valueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $values = new ArrayCollection();

        foreach ($data as $attributeCode => $valuesData) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            foreach ($valuesData as $valueData) {
                $value = $this->denormalizer->denormalize(
                    $valueData,
                    $this->valueClass,
                    'json',
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
        return $type === static::PRODUCT_VALUES_TYPE && in_array($format, $this->supportedFormats);
    }
}
