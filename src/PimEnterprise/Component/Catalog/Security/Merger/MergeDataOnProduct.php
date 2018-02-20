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

namespace PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
     * @param NotGrantedDataMergerInterface[]  $mergers
     * @param AttributeRepositoryInterface     $attributeRepository
     * @param AddParent                        $addParent
     * @param ProductModelRepositoryInterface  $productModelRepository
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

    public function merge($filteredProduct, $fullProduct = null)
    {
        if (!$filteredProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProduct), ProductInterface::class);
        }

        if ($filteredProduct instanceof EntityWithFamilyVariantInterface) {
            $filteredProduct = $this->setParent($filteredProduct);
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
            $filteredProduct->getValue($identifierCode)->getAttribute(),
            null,
            null,
            $filteredProduct->getIdentifier()
        ));

        if ($filteredProduct instanceof EntityWithFamilyVariantInterface) {
            if ($fullProduct instanceof EntityWithFamilyVariantInterface) {
                $fullProduct->setFamilyVariant($filteredProduct->getFamilyVariant());
            } elseif (null !== $filteredProduct->getParent()) {
                $fullProduct = $this->addParent->to($fullProduct, $filteredProduct->getParent()->getCode());
            }

            $fullProduct = $this->setParent($fullProduct);
        }

        foreach ($this->mergers as $merger) {
            $fullProduct = $merger->merge($filteredProduct, $fullProduct);
        }

        return $fullProduct;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return EntityWithFamilyVariantInterface
     */
    private function setParent(EntityWithFamilyVariantInterface $entityWithFamilyVariant): EntityWithFamilyVariantInterface
    {
        if (null === $entityWithFamilyVariant->getParent()) {
            return $entityWithFamilyVariant;
        }

        $parent = $this->productModelRepository->find($entityWithFamilyVariant->getParent()->getId());
        $entityWithFamilyVariant->setParent($parent);

        return $entityWithFamilyVariant;
    }
}
