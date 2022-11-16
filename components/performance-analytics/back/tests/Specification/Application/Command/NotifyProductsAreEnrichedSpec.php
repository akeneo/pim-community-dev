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

namespace Specification\Akeneo\PerformanceAnalytics\Application\Command;

use Akeneo\PerformanceAnalytics\Application\Command\NotifyProductsAreEnriched;
use Akeneo\PerformanceAnalytics\Application\Command\ProductIsEnriched;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class NotifyProductsAreEnrichedSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(NotifyProductsAreEnriched::class);
    }

    public function it_cannot_be_constructed_with_bad_array()
    {
        $this->beConstructedWith([
            new ProductIsEnriched(Uuid::uuid4(), ChannelCode::fromString('ecommerce'), LocaleCode::fromString('en_US'), new \DateTimeImmutable()),
            new \stdClass(),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_product_is_enriched_list()
    {
        $enrichedAt = new \DateTimeImmutable();
        $list = [
            new ProductIsEnriched(Uuid::uuid4(), ChannelCode::fromString('ecommerce'), LocaleCode::fromString('en_US'), $enrichedAt),
            new ProductIsEnriched(Uuid::uuid4(), ChannelCode::fromString('ecommerce'), LocaleCode::fromString('fr_FR'), $enrichedAt),
        ];
        $this->beConstructedWith($list);

        $this->getProductsAreEnriched()->shouldReturn($list);
    }

    public function it_returns_product_uuids()
    {
        $enrichedAt = new \DateTimeImmutable();
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();

        $this->beConstructedWith([
            new ProductIsEnriched($uuid1, ChannelCode::fromString('ecommerce'), LocaleCode::fromString('en_US'), $enrichedAt),
            new ProductIsEnriched($uuid2, ChannelCode::fromString('ecommerce'), LocaleCode::fromString('en_US'), $enrichedAt),
            new ProductIsEnriched($uuid1, ChannelCode::fromString('ecommerce'), LocaleCode::fromString('fr_FR'), $enrichedAt),
            new ProductIsEnriched($uuid3, ChannelCode::fromString('ecommerce'), LocaleCode::fromString('fr_FR'), $enrichedAt),
        ]);

        $this->getProductUuids()->shouldReturn([$uuid1, $uuid2, $uuid3]);
    }
}
