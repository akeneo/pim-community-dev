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
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class MergeDataOnProductModel implements NotGrantedDataMergerInterface
{
    /** @var NotGrantedDataMergerInterface[] */
    private $mergers;

    /**
     * @param NotGrantedDataMergerInterface[] $mergers
     */
    public function __construct(array $mergers)
    {
        $this->mergers = $mergers;
    }

    public function merge($filteredProductModel, $fullProductModel)
    {
        if (!$filteredProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProductModel), ProductModelInterface::class);
        }

        if (!$fullProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProductModel), ProductModelInterface::class);
        }

        if (null === $fullProductModel) {
            return $filteredProductModel;
        }

        $fullProductModel->setCode($filteredProductModel->getCode());
        $fullProductModel->setRoot($filteredProductModel->getRoot());
        $fullProductModel->setLeft($filteredProductModel->getLeft());
        $fullProductModel->setRight($filteredProductModel->getRight());
        $fullProductModel->setParent($filteredProductModel->getParent());
        $fullProductModel->setLevel($filteredProductModel->getLevel());
        $fullProductModel->setFamilyVariant($filteredProductModel->getFamilyVariant());

        foreach ($this->mergers as $merger) {
            $fullProductModel = $merger->merge($filteredProductModel, $fullProductModel);
        }

        return $fullProductModel;
    }
}
