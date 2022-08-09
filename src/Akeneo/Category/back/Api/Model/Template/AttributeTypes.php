<?php
declare(strict_types=1);

namespace Akeneo\Category\Api\Model\Template;

/**
 * Definition of attribute types
 *
 * @phpstan-type AttributeType AttributeTypes::CATEGORY_TEMPLATE_ATTRIBUTE_TYPE_TEXT | AttributeTypes::CATEGORY_TEMPLATE_ATTRIBUTE_TYPE_IMAGE
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeTypes
{
    const TEXT = 'text';
    const IMAGE = 'image';
}

