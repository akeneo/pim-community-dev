<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query;

use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GetGroupedTargetsInterface;
use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GetGroupedTargetsQuery;
use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GroupedTargetsResult;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\Attribute;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\FindViewableAttributesInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\FindSystemTargetsInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetGroupedTargets implements GetGroupedTargetsInterface
{
    private const FIELD_TRANSLATION_BASE = 'pim_common.';
    private const SYSTEM_GROUP_TRANSLATION_KEY = 'System';

    public function __construct(
        private FindViewableAttributesInterface $findViewableAttributes,
        private FindSystemTargetsInterface $findSystemTargets,
        private TranslatorInterface $translator,
    ) {
    }

    public function get(GetGroupedTargetsQuery $query): GroupedTargetsResult
    {
        $locale = $query->locale;
        $limit = $query->limit;
        $systemOffset = $query->systemOffset;
        $attributeOffset = $query->attributeOffset;
        $search = $query->search;

        $paginatedFields = $this->findSystemTargets->execute($locale, $limit, $systemOffset, $search);

        $limit -= count($paginatedFields);

        $attributesResult = $this->findViewableAttributes->execute($locale, $limit, $attributeOffset, $search);

        return new GroupedTargetsResult(
            array_merge(
                $this->formatSystemFields($paginatedFields, $locale),
                $this->formatAttributes($attributesResult->getAttributes()),
            ),
            [
                'system' => $systemOffset + count($paginatedFields),
                'attribute' => $attributesResult->getOffset(),
            ],
        );
    }

    private function formatSystemFields(array $fields, string $localeCode): array
    {
        if (empty($fields)) {
            return [];
        }

        $children = array_map(fn (string $field): array => [
            'code' => $field,
            'type' => 'property',
            'label' => $this->translator->trans(
                sprintf('%s%s', self::FIELD_TRANSLATION_BASE, $field),
                [],
                null,
                $localeCode,
            ),
        ], $fields);

        return [[
            'code' => 'system',
            'label' => $this->translator->trans(self::SYSTEM_GROUP_TRANSLATION_KEY, [], null, $localeCode),
            'children' => $children,
        ]];
    }

    /**
     * @param Attribute[] $attributes
     */
    private function formatAttributes(array $attributes): array
    {
        $results = [];
        foreach ($attributes as $attribute) {
            $groupCode = $attribute->getAttributeGroupCode();
            if (!array_key_exists($groupCode, $results)) {
                $results[$groupCode] = [
                    'code' => $groupCode,
                    'label' => $attribute->getAttributeGroupLabel(),
                    'children' => [],
                ];
            }

            $results[$groupCode]['children'][] = [
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
                'type' => 'attribute',
            ];
        }

        return array_values($results);
    }
}
