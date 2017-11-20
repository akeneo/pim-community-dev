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

namespace PimEnterprise\Component\Catalog\Security\Applier;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ApplyDataOnProduct implements ApplierInterface
{
    /** @var NotGrantedDataMergerInterface */
    private $valuesMerger;

    /** @var NotGrantedDataMergerInterface */
    private $associationMerger;

    /** @var NotGrantedDataMergerInterface */
    private $categoryMerger;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param NotGrantedDataMergerInterface $valuesMerger
     * @param NotGrantedDataMergerInterface $associationMerger
     * @param NotGrantedDataMergerInterface $categoryMerger
     * @param ProductRepositoryInterface    $productRepository
     * @param AttributeRepositoryInterface  $attributeRepository
     */
    public function __construct(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->valuesMerger = $valuesMerger;
        $this->associationMerger = $associationMerger;
        $this->categoryMerger = $categoryMerger;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function apply($filteredProduct)
    {
        if (!$filteredProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProduct), ProductInterface::class);
        }

        if (null === $filteredProduct->getId()) {
            return $filteredProduct;
        }

        $fullProduct = $this->productRepository->find($filteredProduct->getId());
        if (null === $fullProduct) {
            return $filteredProduct;
        }


        $fullProduct->setEnabled($filteredProduct->isEnabled());
        $fullProduct->setFamily($filteredProduct->getFamily());
        $fullProduct->setFamilyId($filteredProduct->getFamilyId());

        $identifierCode = $this->attributeRepository->getIdentifierCode();
        $fullProduct->setIdentifier(new ScalarValue(
            $filteredProduct->getValue($identifierCode)->getAttribute(),
            null,
            null,
            $filteredProduct->getIdentifier()
        ));

        if ($filteredProduct instanceof VariantProductInterface) {
            $fullProduct->setParent($filteredProduct->getParent());
            $fullProduct->setFamilyVariant($filteredProduct->getFamilyVariant());
        }

        $this->valuesMerger->merge($filteredProduct, $fullProduct);
        $this->categoryMerger->merge($filteredProduct, $fullProduct);
        $this->associationMerger->merge($filteredProduct, $fullProduct);

        return $fullProduct;
    }
}
