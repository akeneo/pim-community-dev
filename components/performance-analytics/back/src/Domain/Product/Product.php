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
     * @param array<CategoryCode> $categories
     */
    private function __construct(
        private UuidInterface $uuid,
        private \DateTimeImmutable $createdAt,
        private ?FamilyCode $familyCode,
        private array $categories
    ) {
        Assert::allIsInstanceOf($this->categories, CategoryCode::class);
    }

    /**
     * @param array<CategoryCode> $categories
     */
    public static function fromProperties(
        UuidInterface $uuid,
        \DateTimeImmutable $createdAt,
        ?FamilyCode $familyCode,
        array $categories
    ): Product {
        return new Product($uuid, $createdAt, $familyCode, $categories);
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
    public function categories(): array
    {
        return $this->categories;
    }
}
