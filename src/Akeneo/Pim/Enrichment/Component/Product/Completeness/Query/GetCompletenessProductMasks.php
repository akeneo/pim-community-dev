<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCompletenessProductMasks
{
    /**
     * @param string[] $productIdentifiers
     *
     * @return CompletenessProductMask[]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array;
}
