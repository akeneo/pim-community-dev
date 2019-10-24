<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * TODO: Move this file in AttributeOption/Query/PublicApi when the polish will be done in Structure
 *
 * Warning: Only the couple attribute code/option code is unique
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetExistingAttributeOptionCodes
{
    /**
     * Get all existing option codes among a list of option codes, for several attribute at once.
     *
     * @param array $optionCodesIndexedByAttributeCodes ['color' => ['blue', 'red'], 'brand' => ['abscscd', 'weryet']]
     */
    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array;
}
