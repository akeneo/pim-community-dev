<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScoreCollection;

interface GetLatestProductScoresQueryInterface
{
    /**
     * @param string[] $productIdentifiers
     * @return ProductScoreCollection[]
     */
    public function byProductIdentifiers(array $productIdentifiers): array;

    public function byProductIdentifier(string $productIdentifier): ProductScoreCollection;
}
