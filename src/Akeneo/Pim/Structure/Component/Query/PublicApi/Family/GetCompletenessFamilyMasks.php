<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMask;

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
     *
     * @throws NonExistingFamiliesException
     */
    public function fromFamilyCodes(array $familyCodes): array;
}
