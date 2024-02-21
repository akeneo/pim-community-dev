<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCompletenessProductMasks
{
    /**
     * Calculates completeness masks from a value collection. It is only useful to calculate the missing required
     * attributes for a product model, or a product whose values were potentially updated before rendering in the PEF
     * (e.g permissions). It SHOULD NOT be used for any other purpose.
     * @param string|int $id
     */
    public function fromValueCollection($id, string $familyCode, WriteValueCollection $values): CompletenessProductMask;

    /**
     * @param UuidInterface[] $productUuids
     *
     * @return CompletenessProductMask[]
     */
    public function fromProductUuids(array $productUuids): array;
}
