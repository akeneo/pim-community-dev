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

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Factory\ProductDraftFactory;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var ValueCollectionFactoryInterface */
    protected $valueCollectionFactory;

    /** @var ValueFactory */
    protected $valueFactory;

    /**
     * @param NormalizerInterface                   $normalizer
     * @param ComparatorRegistry                    $comparatorRegistry
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param ProductDraftFactory                   $factory
     * @param ProductDraftRepositoryInterface       $productDraftRepo
     * @param ValueCollectionFactoryInterface       $valueCollectionFactory
     * @param ValueFactory                          $valueFactory
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $productDraftRepo,
        ValueCollectionFactoryInterface $valueCollectionFactory,
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
        $values = $product->isVariant() ? $product->getValuesForVariation() : $product->getValues();
        $newValues = $this->normalizer->normalize($values, 'standard');
        $originalValues = $this->getOriginalValues($product);

        $values = [];
        foreach ($newValues as $code => $newValue) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);

            if (null === $attribute) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s".', $code));
            }

            foreach ($newValue as $index => $changes) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($attribute->getType());
                $diffAttribute = $comparator->compare(
                    $changes,
                    $this->getOriginalValue($originalValues, $code, $changes['locale'], $changes['scope'])
                );

                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;

                    $attribute = $this->attributeRepository->findOneByIdentifier($code);
                    $values[] = $this->valueFactory->create(
                        $attribute,
                        $changes['scope'],
                        $changes['locale'],
                        $changes['data']
                    );
                }
            }
        }

        if (!empty($diff)) {
            $productDraft = $this->getProductDraft($product, $username);
            $productDraft->setValues(new ValueCollection($values));
            $productDraft->setChanges($diff);
            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT);

            return $productDraft;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return EntityWithValuesDraftInterface
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
     * @param array       $originalValues
     * @param string      $code
     * @param null|string $locale
     * @param null|string $channel
     *
     * @return array
     */
    protected function getOriginalValue(array $originalValues, string $code, ?string $locale, ?string $channel): array
    {
        if (!isset($originalValues[$code])) {
            return [];
        }

        foreach ($originalValues[$code] as $originalValue) {
            if ($originalValue['locale'] === $locale && $originalValue['scope'] === $channel) {
                return $originalValue;
            }
        }

        return [];
    }
}
