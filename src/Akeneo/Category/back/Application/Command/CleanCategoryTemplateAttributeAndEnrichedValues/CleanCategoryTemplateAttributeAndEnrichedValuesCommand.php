<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryTemplateAttributeAndEnrichedValuesCommand
{
    public function __construct(
        public readonly string $templateUuid,
        public readonly string $attributeUuid,
    ) {
    }
}
