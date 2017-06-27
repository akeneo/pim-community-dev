<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Factory\ValueCollectionFactory;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Factory\ProductDraftFactory;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Draft builder to have modifications on product values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftBuilder implements ProductDraftBuilderInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var ValueCollectionFactory */
    protected $valueCollectionFactory;

    /** @var ValueFactory */
    protected $valueFactory;

    /**
     * @param NormalizerInterface             $normalizer
     * @param ComparatorRegistry              $comparatorRegistry
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $productDraftRepo
     * @param ValueCollectionFactory          $valueCollectionFactory
     * @param ValueFactory                    $valueFactory
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $productDraftRepo,
        ValueCollectionFactory $valueCollectionFactory,
        ValueFactory $valueFactory
    ) {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->factory = $factory;
        $this->productDraftRepo = $productDraftRepo;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ProductInterface $product, $username)
    {
        $newValues = $this->normalizer->normalize($product->getValues(), 'standard');
        $originalValues = $this->getOriginalValues($product);
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($newValues));

        $diff = [];
        foreach ($newValues as $code => $new) {
            if (!isset($attributeTypes[$code])) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s".', $code));
            }

            foreach ($new as $index => $changes) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($attributeTypes[$code]);
                $diffAttribute = $comparator->compare(
                    $changes,
                    $this->getOriginalValue($originalValues, $code, $index)
                );

                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;
                }
            }
        }

        if (!empty($diff)) {
            $productDraft = $this->getProductDraft($product, $username);
            $productDraft->setChanges($diff);
            $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_DRAFT);

            return $productDraft;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraftInterface
     */
    protected function getProductDraft(ProductInterface $product, $username)
    {
        if (null === $productDraft = $this->productDraftRepo->findUserProductDraft($product, $username)) {
            $productDraft = $this->factory->createProductDraft($product, $username);
        }

        return $productDraft;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOriginalValues(ProductInterface $product)
    {
        $rawValues = $product->getRawValues();
        $originalValues = $this->valueCollectionFactory->createFromStorageFormat($rawValues);

        return $this->normalizer->normalize($originalValues, 'standard');
    }

    /**
     * @param array  $originalValues
     * @param string $code
     * @param int    $index
     *
     * @return array
     */
    protected function getOriginalValue(array $originalValues, $code, $index)
    {
        return !isset($originalValues[$code][$index]) ? [] : $originalValues[$code][$index];
    }
}
