<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;

/**
 * Provides uuid mapping from product identifiers
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetUuidMappingFromProductIdentifiersQueryInterface
{
    /**
     * @param string[] $productIdentifiers
     *
     * @return UuidMapping
     */
    public function execute(array $productIdentifiers): UuidMapping;
}
