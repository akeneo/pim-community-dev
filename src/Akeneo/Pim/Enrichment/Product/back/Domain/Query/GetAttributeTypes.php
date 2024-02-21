<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAttributeTypes
{
    /**
     * @param string[] $attributeCodes
     * @return array<string, string>, example:
     *  {
     *      "sku": "pim_catalog_identifier",
     *      "name": "pim_catalog_text",
     *      ...
     *  }
     */
    public function fromAttributeCodes(array $attributeCodes): array;
}
