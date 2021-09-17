<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SearchAttributeOptionsInterface
{
    /**
     * @return AttributeOption[]
     */
    public function search(SearchAttributeOptionsParameters $searchParameters): array;
}
