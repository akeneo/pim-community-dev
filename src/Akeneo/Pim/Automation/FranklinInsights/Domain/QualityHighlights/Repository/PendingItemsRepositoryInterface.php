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

interface PendingItemsRepositoryInterface
{
    public function addUpdatedAttributeCode(string $code): void;

    public function addDeletedAttributeCode(string $code): void;

    public function addUpdatedFamilyCode(string $code): void;

    public function addDeletedFamilyCode(string $code): void;

    public function addUpdatedProductId(int $identifier): void;

    public function addDeletedProductId(int $identifier): void;
}
