<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;

interface PendingItemsRepositoryInterface
{
    public function addUpdatedAttributeCode(string $code): void;

    public function addDeletedAttributeCode(string $code): void;

    public function addUpdatedFamilyCode(string $code): void;

    public function addDeletedFamilyCode(string $code): void;

    public function addUpdatedProductId(int $identifier): void;

    public function addDeletedProductId(int $identifier): void;

    public function acquireLock(Lock $lock): void;

    public function removeUpdatedAttributes(array $attributeCodes, Lock $lock): void;

    public function releaseUpdatedAttributesLock(array $attributeCodes, Lock $lock): void;

    public function removeDeletedAttributes(array $attributeCodes, Lock $lock): void;

    public function releaseDeletedAttributesLock(array $attributeCodes, Lock $lock): void;

    public function removeUpdatedFamilies(array $familyCodes, Lock $lock): void;

    public function releaseUpdatedFamiliesLock(array $familyCodes, Lock $lock): void;

    public function removeDeletedFamilies(array $familyCodes, Lock $lock): void;

    public function releaseDeletedFamiliesLock(array $familyCodes, Lock $lock): void;

    public function removeUpdatedProducts(array $productIds, Lock $lock): void;

    public function releaseUpdatedProductsLock(array $productIds, Lock $lock): void;

    public function removeDeletedProducts(array $productIds, Lock $lock): void;

    public function releaseDeletedProductsLock(array $productIds, Lock $lock): void;

    public function fillWithAllAttributes(): void;

    public function fillWithAllFamilies(): void;

    public function fillWithAllProducts(): void;
}
