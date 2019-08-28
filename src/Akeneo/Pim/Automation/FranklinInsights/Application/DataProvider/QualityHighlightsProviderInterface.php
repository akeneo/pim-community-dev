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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider;

interface QualityHighlightsProviderInterface
{
    public function applyAttributeStructure(array $attributes): void;

    public function deleteAttribute(string $attributeCode): void;

    public function applyFamilies(array $familyCodes): void;

    public function deleteFamily(string $familyCode): void;
}
