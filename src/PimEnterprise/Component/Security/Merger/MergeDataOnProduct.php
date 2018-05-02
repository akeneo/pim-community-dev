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

namespace PimEnterprise\Component\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\EntityWithFamilyVariant\AddParent;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;

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
        $fullProduct->setIdentifier(new ScalarValue(
            $fullProduct->getValue($identifierCode)->getAttribute(),
            null,
            null,
            $filteredProduct->getIdentifier()
        ));

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
