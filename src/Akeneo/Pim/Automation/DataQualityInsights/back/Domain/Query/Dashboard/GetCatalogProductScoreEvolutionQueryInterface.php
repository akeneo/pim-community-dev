<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

interface GetCatalogProductScoreEvolutionQueryInterface
{
    public function byCatalog(ChannelCode $channel, LocaleCode $locale): array;

    public function byCategory(ChannelCode $channel, LocaleCode $locale, CategoryCode $category): array;

    public function byFamily(ChannelCode $channel, LocaleCode $locale, FamilyCode $family): array;
}
