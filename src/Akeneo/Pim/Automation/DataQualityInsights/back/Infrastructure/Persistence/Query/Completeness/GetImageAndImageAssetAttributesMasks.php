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

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;

class GetImageAndImageAssetAttributesMasks implements GetRequiredAttributesMasks
{
    public function __construct(
        private GetRequiredAttributesMasks                  $getRequiredAttributesMasks,
        private FilterImageAndImageAssetAttributesInterface $filterImageAndImageAssetAttributes
    ) {
    }

    public function fromFamilyCodes(array $familyCodes): array
    {
        $masks = $this->getRequiredAttributesMasks->fromFamilyCodes($familyCodes);
        return $this->filterImageAndImageAssetAttributes->filter($familyCodes, $masks);
    }
}
