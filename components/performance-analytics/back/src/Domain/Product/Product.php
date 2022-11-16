<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class Product
{
    /**
     * @param array<CategoryCode> $categoryCodes
     * @param array<CategoryCode> $categoryWithAncestorsCodes
     */
    private function __construct(
        private UuidInterface $uuid,
        private \DateTimeImmutable $createdAt,
        private ?FamilyCode $familyCode,
        private array $categoryCodes,
        private array $categoryWithAncestorsCodes
    ) {
        Assert::allIsInstanceOf($this->categoryCodes, CategoryCode::class);
        Assert::allIsInstanceOf($this->categoryWithAncestorsCodes, CategoryCode::class);
    }

    /**
     * @param array<CategoryCode> $categoryCodes
     * @param array<CategoryCode> $categoryWithAncestorsCodes
     */
    public static function fromProperties(
        UuidInterface $uuid,
        \DateTimeImmutable $createdAt,
        ?FamilyCode $familyCode,
        array $categoryCodes,
        array $categoryWithAncestorsCodes
    ): Product {
        return new Product($uuid, $createdAt, $familyCode, $categoryCodes, $categoryWithAncestorsCodes);
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function familyCode(): ?FamilyCode
    {
        return $this->familyCode;
    }

    /**
     * @return array<CategoryCode>
     */
    public function categoryCodes(): array
    {
        return $this->categoryCodes;
    }

    /**
     * @return array<CategoryCode>
     */
    public function categoryWithAncestorsCodes(): array
    {
        return $this->categoryWithAncestorsCodes;
    }
}
