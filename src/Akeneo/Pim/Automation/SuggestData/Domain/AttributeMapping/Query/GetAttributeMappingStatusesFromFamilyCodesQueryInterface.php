<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Query;

/**
 * Gets the attribute mapping status for each families of a provided list of family codes.
 * This will return an array like this:
 * [
 *     'family_1' => 0,
 *     'family_2' => 2,
 *     'family_3' => 1,
 * ].
 *
 * Status codes are defined in `Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family` constants.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface GetAttributeMappingStatusesFromFamilyCodesQueryInterface
{
    /**
     * @param string[] $familyCodes
     *
     * @return array
     */
    public function execute(array $familyCodes): array;
}
