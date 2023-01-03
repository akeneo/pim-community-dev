<?php

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 */
interface FindAttributeOptions
{
    /**
     * Returns the attribute options with the label of the current user UI locale
     * Example:
     * [
     *     ['code' => 'option1', 'labels' => ['en_US' => 'Option 1']],
     *     ['code' => 'option2', 'labels' => ['en_US' => '[option2]']],
     * ]
     *
     * @param string[]|null $includeCodes
     *²
     * @return array{
     *     code: string,
     *     labels: array<string, string>
     * }
     */
    public function search(
        string $attributeCode,
        string $search = '',
        int $page = 1,
        int $limit = 20,
        ?array $includeCodes = null,
    ): array;
}
