<?php

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateCategoryTemplateAttributesOrder
{
    public function fromAttributeCollection(AttributeCollection $attributeList): void;
}
