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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class MergeDataOnProductModel implements NotGrantedDataMergerInterface
{
    /** @var NotGrantedDataMergerInterface[] */
    private $mergers;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param NotGrantedDataMergerInterface[] $mergers
     * @param ProductModelRepositoryInterface $productModelRepository
     */
    public function __construct(array $mergers, ProductModelRepositoryInterface $productModelRepository)
    {
        $this->mergers = $mergers;
        $this->productModelRepository = $productModelRepository;
    }

    public function merge($filteredProductModel, $fullProductModel = null)
    {
        if (!$filteredProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProductModel), ProductModelInterface::class);
        }

        $filteredProductModel = $this->setParent($filteredProductModel);

        if (null === $fullProductModel) {
            return $filteredProductModel;
        }

        if (!$fullProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProductModel), ProductModelInterface::class);
        }

        $fullProductModel->setCode($filteredProductModel->getCode());
        $fullProductModel->setParent($filteredProductModel->getParent());
        $fullProductModel->setFamilyVariant($filteredProductModel->getFamilyVariant());

        foreach ($this->mergers as $merger) {
            $fullProductModel = $merger->merge($filteredProductModel, $fullProductModel);
        }

        return $fullProductModel;
    }

    /**
     * Set the parent of the product model.
     * If we want to be able to save correctly the product model, we have to find the full parent known by doctrine.
     *
     * @param ProductModelInterface $filteredProductModel
     *
     * @return ProductModelInterface
     */
    private function setParent(ProductModelInterface $filteredProductModel): ProductModelInterface
    {
        if (null === $filteredProductModel->getParent()) {
            return $filteredProductModel;
        }

        $parent = $this->productModelRepository->find($filteredProductModel->getParent()->getId());
        $filteredProductModel->setParent($parent);

        return $filteredProductModel;
    }
}
