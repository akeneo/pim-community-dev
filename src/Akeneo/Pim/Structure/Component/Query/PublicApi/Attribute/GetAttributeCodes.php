<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAttributeCodes
{
    /**
     * @param string[] $attributeTypes
     * @return string[]
     */
    public function forAttributeTypes(array $attributeTypes): array;
}
