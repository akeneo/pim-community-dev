<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transforms normalized product template values data into value objects and vice versa
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateValuesTransformer implements DataTransformerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var string */
    protected $productValueClass;

    /**
     * Constructor
     *
     * @param NormalizerInterface   $normalizer
     * @param DenormalizerInterface $denormalizer
     * @param AttributeRepository   $attributeRepository
     * @param string                $productValueClass
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        AttributeRepository $attributeRepository,
        $productValueClass
    ) {
        $this->normalizer          = $normalizer;
        $this->denormalizer        = $denormalizer;
        $this->attributeRepository = $attributeRepository;
        $this->productValueClass   = $productValueClass;
    }

    /**
     * Transform normalized values into value objects
     *
     * @param mixed $data
     *
     * @return array
     */
    public function transform($data)
    {
        $values = new ArrayCollection();

        foreach ($data as $attributeCode => $valuesData) {
            $attribute = $this->attributeRepository->findOneByCode($attributeCode);
            foreach ($valuesData as $valueData) {
                $value = $this->denormalizer->denormalize(
                    $valueData,
                    $this->productValueClass,
                    'json',
                    ['attribute' => $attribute]
                );

                $values->add($value);
            }
        }

        return $values;
    }

    /**
     * Transform value objects into normalized values
     *
     * @param array $values
     *
     * @return array
     */
    public function reverseTransform($values)
    {
        $normalizedValues = [];

        foreach ($values as $value) {
            $attributeCode = $value->getAttribute()->getCode();
            $normalizedValues[$attributeCode][] = $this->normalizer->normalize(
                $value,
                'json',
                ['entity' => 'product']
            );
        }

        return $normalizedValues;
    }
}
