<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Category\Domain\Query\UpdateCategoryUpdatedDate;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ACLUpdateCategoryUpdatedDate implements UpdateCategoryUpdatedDate
{
    public function __construct(private UpdateCategoryUpdatedDate $updateCategoryUpdatedDate)
    {
    }

    public function execute(string $categoryCode): void
    {
        $this->updateCategoryUpdatedDate->execute($categoryCode);
    }
}
