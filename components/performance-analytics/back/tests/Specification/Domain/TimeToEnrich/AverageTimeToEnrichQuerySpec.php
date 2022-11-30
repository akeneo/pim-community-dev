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

namespace Specification\Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichQuery;
use PhpSpec\ObjectBehavior;

final class AverageTimeToEnrichQuerySpec extends ObjectBehavior
{
    public function it_can_be_instantiated(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            PeriodType::WEEK,
            [ChannelCode::fromString('mobile')],
            [LocaleCode::fromString('en_US')],
            [FamilyCode::fromString('shoes')],
            [CategoryCode::fromString('tongue')],
        );

        $this->shouldHaveType(AverageTimeToEnrichQuery::class);
    }

    public function it_cannot_be_created_with_invalid_channel_codes(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            PeriodType::WEEK,
            [ChannelCode::fromString('mobile'), 'ecommerce'],
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_created_with_invalid_locale_codes(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            PeriodType::WEEK,
            [ChannelCode::fromString('mobile')],
            [LocaleCode::fromString('en_US'), 'fr_FR']
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_created_with_invalid_family_codes(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            PeriodType::WEEK,
            [ChannelCode::fromString('mobile')],
            [LocaleCode::fromString('en_US')],
            [FamilyCode::fromString('shoes'), 'camera'],
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_created_with_invalid_category_codes(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            PeriodType::WEEK,
            [ChannelCode::fromString('mobile')],
            [LocaleCode::fromString('en_US')],
            [FamilyCode::fromString('shoes')],
            [CategoryCode::fromString('tongue'), 'basket'],
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
