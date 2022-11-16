<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\Product\Product;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ProductSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromProperties', [
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            FamilyCode::fromString('family'),
            [CategoryCode::fromString('category1'), CategoryCode::fromString('category2')],
            [CategoryCode::fromString('category1'), CategoryCode::fromString('category2'), CategoryCode::fromString('A')],
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Product::class);
    }

    public function it_cannot_create_with_an_invalid_category_code()
    {
        $this->beConstructedThrough('fromProperties', [
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            FamilyCode::fromString('family'),
            [
                'category1',
                CategoryCode::fromString('category2'),
            ],
            [],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
