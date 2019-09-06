<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class MergeDataOnProduct implements NotGrantedDataMergerInterface
{
    /** @var NotGrantedDataMergerInterface[] */
    private $mergers;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AddParent */
    private $addParent;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param NotGrantedDataMergerInterface[] $mergers
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param AddParent                       $addParent
     * @param ProductModelRepositoryInterface $productModelRepository
     */
    public function __construct(
        array $mergers,
        AttributeRepositoryInterface $attributeRepository,
        AddParent $addParent,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->mergers = $mergers;
        $this->attributeRepository = $attributeRepository;
        $this->addParent = $addParent;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredProduct, $fullProduct = null)
    {
        if (!$filteredProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($filteredProduct),
                ProductInterface::class
            );
        }

        if ($filteredProduct instanceof EntityWithFamilyVariantInterface) {
            $this->setParent($filteredProduct);
        }

        if (null === $fullProduct) {
            return $filteredProduct;
        }

        if (!$fullProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProduct), ProductInterface::class);
        }

        $fullProduct->setEnabled($filteredProduct->isEnabled());
        $fullProduct->setFamily($filteredProduct->getFamily());
        $fullProduct->setFamilyId($filteredProduct->getFamilyId());
        $fullProduct->setGroups($filteredProduct->getGroups());
        $fullProduct->setUniqueData($filteredProduct->getUniqueData());

        $identifierCode = $this->attributeRepository->getIdentifierCode();
        $fullProduct->getValues()->removeByAttributeCode($identifierCode);
        $fullProduct->addValue(ScalarValue::value($identifierCode, $filteredProduct->getIdentifier()));
        $fullProduct->setIdentifierValue($filteredProduct->getIdentifier());

        if ($filteredProduct->isVariant()) {
            if ($fullProduct->isVariant()) {
                $fullProduct->setFamilyVariant($filteredProduct->getFamilyVariant());
                $fullProduct->setParent($filteredProduct->getParent());
            } else {
                $fullProduct = $this->addParent->to($fullProduct, $filteredProduct->getParent()->getCode());
                $this->setParent($fullProduct);
            }
        }

        foreach ($this->mergers as $merger) {
            $fullProduct = $merger->merge($filteredProduct, $fullProduct);
        }

        return $fullProduct;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     */
    private function setParent(EntityWithFamilyVariantInterface $entityWithFamilyVariant): void
    {
        if (null === $entityWithFamilyVariant->getParent()) {
            return;
        }

        $parent = $this->productModelRepository->find($entityWithFamilyVariant->getParent()->getId());
        $entityWithFamilyVariant->setParent($parent);
    }
}
