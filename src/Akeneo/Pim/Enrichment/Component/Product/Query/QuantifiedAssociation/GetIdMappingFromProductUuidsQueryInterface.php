<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;

/**
 * Provides id mapping from product uuids
 *
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetIdMappingFromProductUuidsQueryInterface
{
    /**
     * @param string[] $productUuids
     *
     * @return UuidMapping
     */
    public function execute(array $productUuids): UuidMapping;
}
