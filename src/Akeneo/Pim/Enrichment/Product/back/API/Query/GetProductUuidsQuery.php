<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsQuery
{
    /**
     * @param array<string, array<mixed>> $searchFilters
     *
     * The format of the search filters is the same as the one used in the external API. For example:
     *  [
     *      'sku' => [
     *          [
     *              'operator' => 'IN',
     *              'value' => ['SKU1', 'SKU2', 'SKU3'],
     *          ],
     *      ],
     *  ]
     */
    public function __construct(
        private array $searchFilters,
        private ?int $userId,
        private ?UuidInterface $searchAfterUuid = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function searchFilters(): array
    {
        return $this->searchFilters;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function searchAfterUuid(): ?UuidInterface
    {
        return $this->searchAfterUuid;
    }
}
