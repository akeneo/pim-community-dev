<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
interface GetGroupedAttributes
{
    /**
     * Returns the attributes for the given attribute types.
     * The locale code is used to find the labels.
     * Return format:
     *  [
     *      {
     *          "code": "name",
     *          "label": "Name",
     *          "group_code": "marketing",
     *          "group_label": "Marketing",
     *      },
     *      {
     *          "code": "description",
     *          "label": "[description]", // Fallback if label does not exist is "[code]"
     *          "group_code": "marketting",
     *          "group_label": "Marketting",
     *      },
     *      ...
     *  ]
     *
     *
     * @param string[] | null $attributeTypes
     */
    public function findAttributes(
        string $localeCode,
        int $limit,
        int $offset = 0,
        ?array $attributeTypes = null,
        ?string $search = null
    ): array;
}
