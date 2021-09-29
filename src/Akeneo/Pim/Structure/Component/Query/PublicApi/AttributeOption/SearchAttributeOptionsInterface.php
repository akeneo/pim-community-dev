<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SearchAttributeOptionsInterface
{
    public function search(
        string $attributeCode,
        SearchAttributeOptionsParameters $searchParameters
    ): SearchAttributeOptionsResult;
}
