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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindQualityScoresInterface;

class FindQualityScores implements FindQualityScoresInterface
{
    private GetProductsWithQualityScoresInterface $getProductsWithQualityScores;

    public function __construct(GetProductsWithQualityScoresInterface $getProductsWithQualityScores)
    {
        $this->getProductsWithQualityScores = $getProductsWithQualityScores;
    }

    public function forProduct(string $productIdentifier): array
    {
        $productWithQualityScores = $this->getProductsWithQualityScores->fromNormalizedProduct($productIdentifier, []);
        return $productWithQualityScores['quality_scores'];
    }
}
