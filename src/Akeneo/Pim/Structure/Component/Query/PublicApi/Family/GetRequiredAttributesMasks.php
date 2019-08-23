<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetRequiredAttributesMasks
{
    /**
     * @param string[] $familyCodes
     *
     * @return RequiredAttributesMask[]
     *
     * @throws NonExistingFamiliesException
     */
    public function fromFamilyCodes(array $familyCodes): array;
}
