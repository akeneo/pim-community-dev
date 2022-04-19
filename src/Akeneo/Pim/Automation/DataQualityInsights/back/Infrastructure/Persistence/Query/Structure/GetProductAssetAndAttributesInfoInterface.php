<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

interface GetProductAssetAndAttributesInfoInterface
{
    /**
     * Given an array of family codes ['family_code', 'family_code_2']
     * Return an array of asset and product attributes ordered by family code
     *
     * @param array<string> $familyCodes
     * @return array<string, array<int, array{attribute_code: string, asset_family_identifier:string}>>
     */
    public function forProductFamilyCodes(array $familyCodes): array;
}
