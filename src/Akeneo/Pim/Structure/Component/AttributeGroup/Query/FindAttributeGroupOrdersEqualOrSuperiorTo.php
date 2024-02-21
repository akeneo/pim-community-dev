<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\AttributeGroup\Query;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;

/**
 * Find all sort orders equals or superior to the given attribute group sort order.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindAttributeGroupOrdersEqualOrSuperiorTo
{
    public function execute(AttributeGroup $attributeGroup): array;
}
