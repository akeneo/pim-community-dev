<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * TODO: Move this file in AttributeOption/Query/PublicApi when the polish will be done in Structure
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetExistingAttributeOptionCodes
{
    public function fromOptionCodes(array $optionCodes): array;
}
