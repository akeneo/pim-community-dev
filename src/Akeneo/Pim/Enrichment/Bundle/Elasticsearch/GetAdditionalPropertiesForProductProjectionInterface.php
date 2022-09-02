<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Ramsey\Uuid\UuidInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAdditionalPropertiesForProductProjectionInterface
{
    /**
     * Returns an associative array of additional properties for the indexation of several products.
     *
     * @param UuidInterface[] $productUuids
     * @param array<string, array> $context
     * @return array
     *      [
     *          'product_1_uuid' => ['key_1_to_index' => 'value_1_to_index', 'key_2_to_index' => 'value_2_to_index'],
     *          'product_2_uuid' => ['key_1_to_index' => 'value_3_to_index']
     *      ]
     */
    public function fromProductUuids(array $productUuids, array $context): array;
}
