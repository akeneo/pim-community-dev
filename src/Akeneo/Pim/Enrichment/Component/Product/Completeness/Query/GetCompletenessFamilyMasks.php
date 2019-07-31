<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessFamilyMask;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCompletenessFamilyMasks
{
    /**
     * @param string[] $familyCodes
     *
     * @return CompletenessFamilyMask[]
     */
    public function fromFamilyCodes(array $familyCodes): array;
}
