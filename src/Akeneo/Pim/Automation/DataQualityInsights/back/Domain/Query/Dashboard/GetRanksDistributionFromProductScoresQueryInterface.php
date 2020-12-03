<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;

interface GetRanksDistributionFromProductScoresQueryInterface
{
    public function forWholeCatalog(\DateTimeImmutable $date): RanksDistributionCollection;

    public function byCategory(CategoryCode $categoryCode, \DateTimeImmutable $date): RanksDistributionCollection;

    public function byFamily(FamilyCode $familyCode, \DateTimeImmutable $date): RanksDistributionCollection;
}
