<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductCategoryCodesQuery
{
    /**
     * @param UuidInterface[] $productUuids
     */
    public function __construct(public readonly array $productUuids)
    {
    }
}
