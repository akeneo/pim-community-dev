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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Write\AsyncRequest;

interface QualityHighlightsProviderInterface
{
    /**
     * @param AsyncRequest[] $asyncRequests
     */
    public function applyAsyncAttributeStructure(array $asyncRequests): void;

    public function deleteAttribute(string $attributeCode): void;

    public function applyFamilies(array $families): void;

    public function deleteFamily(string $familyCode): void;

    public function applyProducts(array $products): void;

    public function deleteProduct(int $productId): void;
}
