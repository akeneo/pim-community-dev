<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

use Akeneo\Catalogs\Domain\ProductSelection\Criterion;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateCatalogProductSelectionCriteriaQueryInterface
{
    /**
     * @param array<Criterion> $productSelectionCriteria
     */
    public function execute(
        string $id,
        array $productSelectionCriteria,
    ): void;
}
