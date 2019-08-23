<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeCodesFromIdsQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingAttributesIdQueryInterface;

class SynchronizeAttributesWithFranklin
{
    /** @var SelectPendingAttributesIdQueryInterface */
    private $pendingAttributesQuery;

    /** @var ApplyAttributeStructure */
    private $applyAttributeStructure;

    /** @var SelectAttributeCodesFromIdsQueryInterface */
    private $selectAttributeCodesFromIdsQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    public function __construct(
        SelectPendingAttributesIdQueryInterface $pendingAttributesQuery,
        ApplyAttributeStructure $applyAttributeStructure,
        SelectAttributeCodesFromIdsQueryInterface $selectAttributeCodesFromIdsQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->pendingAttributesQuery = $pendingAttributesQuery;
        $this->applyAttributeStructure = $applyAttributeStructure;
        $this->selectAttributeCodesFromIdsQuery = $selectAttributeCodesFromIdsQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
    }

    public function synchronize(int $batchSize)
    {
        $this->synchronizeUpdatedAttributes($batchSize);
        $this->synchronizeDeletedAttributes($batchSize);
    }

    private function synchronizeUpdatedAttributes(int $batchSize)
    {
        $index = 0;
        while (true) {
            $attributeIds = $this->pendingAttributesQuery->getUpdatedAttributeIds($index, $batchSize);
            if (! empty($attributeIds)) {
                $this->applyAttributeStructure->apply($attributeIds);
            }

            if (count($attributeIds) < $batchSize) {
                break;
            }

            $index += $batchSize;
        }
    }

    private function synchronizeDeletedAttributes(int $batchSize)
    {
        $index = 0;
        while (true) {
            $attributeIds = $this->pendingAttributesQuery->getDeletedAttributeIds($index, $batchSize);
            if (! empty($attributeIds)) {
                $attributeCodes = $this->selectAttributeCodesFromIdsQuery->execute($attributeIds);
                foreach ($attributeCodes as $attributeCode) {
                    $this->qualityHighlightsProvider->deleteAttribute($attributeCode);
                }
            }

            if (count($attributeIds) < $batchSize) {
                break;
            }

            $index += $batchSize;
        }
    }
}
