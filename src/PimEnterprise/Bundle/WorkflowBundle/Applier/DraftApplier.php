<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Applier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ChainedComparator;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Draft applier to compare a product with some modifications
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DraftApplier implements ApplierInterface
{
    /** @var array */
    protected $originalValues;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var ChainedComparator */
    protected $comparator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param SerializerInterface          $serializer
     * @param ComparatorInterface          $comparator
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SerializerInterface $serializer,
        ChainedComparator $comparator,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->serializer          = $serializer;
        $this->comparator          = $comparator;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {$inheritdoc}
     */
    public function applier(ProductInterface $product)
    {
        $newValues = $this->serializer->normalize($product->getValues(), 'json');

        $diff = [];
        foreach ($newValues as $code => $new) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);
            if (null === $attribute) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s". ', $code));
            }

            foreach ($new as $i => $changes) {
                $diffAttribute = $this->comparator->compare(
                    $attribute->getAttributeType(),
                    $changes,
                    $this->getOriginalValue($code, $i)
                );
                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;
                }
            }
        }

        return $diff;
    }

    /**
     * {$inheritdoc}
     */
    public function saveOriginalValues(ProductInterface $product)
    {
        $this->originalValues = $this->serializer->normalize($product->getValues(), 'json');

        return $this;
    }

    /**
     * @param string $code
     * @param int    $i
     *
     * @return array
     */
    protected function getOriginalValue($code, $i)
    {
        $originalValues = $this->originalValues;

        return !isset($originalValues[$code][$i]) ? [] : $originalValues[$code][$i];
    }
}
