<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\GetProductModelAttributesMaskQueryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;

class GetAttributeTypesProductModelMasksWithAssetsQuery implements GetProductModelAttributesMaskQueryInterface
{
    public function __construct(
        private GetAttributeTypesProductModelMasksQuery $getAttributeTypesProductModelMasksQuery,
        private FilterImageAndImageAssetAttributesInterface $filterImageAndImageAssetAttributes
    ) {
    }

    public function execute(ProductModelId $productModelId): ?RequiredAttributesMask
    {
        $mask = $this->getAttributeTypesProductModelMasksQuery->execute($productModelId);
        if (null === $mask) {
            return null;
        }

        $maskFiltered = $this->filterImageAndImageAssetAttributes->filter([$mask->getFamilyCode()], [$mask]);

        return empty($maskFiltered) ? null : $maskFiltered[$mask->getFamilyCode()];
    }
}
