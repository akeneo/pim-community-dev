<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
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

    /** @var SmartManagerRegistry */
    protected $registry;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param DenormalizerInterface $denormalizer
     * @param SmartManagerRegistry  $registry
     * @param string                $valueClass
     * @param string                $attributeClass
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        SmartManagerRegistry $registry,
        $valueClass,
        $attributeClass
    ) {
        $this->denormalizer   = $denormalizer;
        $this->registry       = $registry;
        $this->valueClass     = $valueClass;
        $this->attributeClass = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $values = new ArrayCollection();
        $attributeRepo = $this->registry->getRepository($this->attributeClass);

        foreach ($data as $attributeCode => $valuesData) {
            $attribute = $attributeRepo->findOneByIdentifier($attributeCode);

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
