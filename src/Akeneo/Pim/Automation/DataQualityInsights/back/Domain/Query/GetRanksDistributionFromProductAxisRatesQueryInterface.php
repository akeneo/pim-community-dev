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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;

/**
 * Return an array containing the distribution of the ranks per axis/channel/locale
 * Ex:
 *  [
 *      "consistency" => [
 *          "mobile" => [
 *              "en_US" => [
 *                  "rank_1" => 25,
 *                  "rank_2" => 27,
 *                  "rank_3" => 36,
 *                  "rank_4" => 37,
 *                  "rank_5" => 36
 *              ]
 *          ],
 *          "ecommerce" => [
 *              "en_US" => [
 *                  "rank_1" => 33,
 *                  "rank_2" => 33,
 *                  "rank_3" => 28,
 *                  "rank_4" => 29,
 *                  "rank_5" => 38
 *              ]
 *          ]
 *      ],
 *      "enrichment" => [
 *          "ecommerce" => [
 *              "en_US" => [
 *                  "rank_1" => 33,
 *                  "rank_2" => 33,
 *                  "rank_3" => 28,
 *                  "rank_4" => 29,
 *                  "rank_5" => 38
 *              ]
 *          ]
 *      ]
 *  ];
 */
interface GetRanksDistributionFromProductAxisRatesQueryInterface
{
    public function forWholeCatalog(\DateTimeImmutable $date): array;

    public function byCategory(CategoryCode $categoryCode, \DateTimeImmutable $date): array;

    public function byFamily(FamilyCode $familyCode, \DateTimeImmutable $date): array;
}
