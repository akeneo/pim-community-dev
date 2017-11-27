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
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /**
     * @param NotGrantedDataMergerInterface        [] $mergers
     * @param AttributeRepositoryInterface          $attributeRepository
     */
    public function __construct(
        array $mergers,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->mergers = $mergers;
        $this->attributeRepository = $attributeRepository;
    }

    public function merge($filteredProduct, $fullProduct)
    {
        if (!$filteredProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProduct), ProductInterface::class);
        }

        if (!$fullProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProduct), ProductInterface::class);
        }

        if (null === $filteredProduct->getId() || null === $fullProduct) {
            return $filteredProduct;
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
            $fullProduct->setParent($filteredProduct->getParent());
            $fullProduct->setFamilyVariant($filteredProduct->getFamilyVariant());
        }

        foreach ($this->mergers as $merger) {
            $fullProduct = $merger->merge($filteredProduct, $fullProduct);
        }

        return $fullProduct;
    }
}
