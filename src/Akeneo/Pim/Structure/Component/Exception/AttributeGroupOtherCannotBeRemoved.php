<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Component\Exception;

class AttributeGroupOtherCannotBeRemoved extends UserFacingError
{
    public static function create()
    {
        return new self('pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed');
    }
}
