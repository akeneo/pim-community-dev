<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\FindAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Platform\TailoredExport\Domain\Query\FindSystemSourcesInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindViewableAttributesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetGroupedSourcesAction
{
    private const LIMIT_DEFAULT = 20;
    private const FIELD_TRANSLATION_BASE = 'pim_common.';
    private const SYSTEM_GROUP_TRANSLATION_KEY = 'System';
    private const DEFAULT_LOCALE = 'en_US';

    private TranslatorInterface $translator;
    private FindSystemSourcesInterface $getSystemSources;
    private FindAssociationTypesInterface $findAssociationTypes;
    private FindViewableAttributesInterface $findViewableAttributes;

    public function __construct(
        TranslatorInterface $translator,
        FindSystemSourcesInterface $getSystemSources,
        FindAssociationTypesInterface $findAssociationTypes,
        FindViewableAttributesInterface $findViewableAttributes
    ) {
        $this->translator = $translator;
        $this->getSystemSources = $getSystemSources;
        $this->findAssociationTypes = $findAssociationTypes;
        $this->findViewableAttributes = $findViewableAttributes;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $options = $request->get('options', []);
        $search = $request->get('search');
        $limit = $options['limit'] ?? self::LIMIT_DEFAULT;
        $systemOffset = (int) $options['offset']['system'];
        $associationTypeOffset = (int) $options['offset']['association_type'];
        $attributeOffset = (int) $options['offset']['attribute'];

        $localeCode = $options['locale'] ?? self::DEFAULT_LOCALE;
        $attributeTypes = isset($options['attributeTypes']) ? explode(',', $options['attributeTypes']) : null;

        $paginatedFields = $this->getSystemSources->execute($localeCode, $limit, $systemOffset, $search);
        $limit -= count($paginatedFields);

        $paginatedAssociations = $this->findAssociationTypes->execute($localeCode, $limit, $associationTypeOffset, $search);
        $limit -= count($paginatedAssociations);

        $attributesResult = $this->findViewableAttributes->execute(
            $localeCode,
            $limit,
            $attributeTypes,
            $attributeOffset,
            $search
        );

        return new JsonResponse([
            'results' => array_merge(
                $this->formatSystemFields($paginatedFields, $localeCode),
                $this->formatAssociationFields($paginatedAssociations, $localeCode),
                $this->formatAttributes($attributesResult->getAttributes())
            ),
            'offset' => [
                'system' => $systemOffset + count($paginatedFields),
                'association_type' => $associationTypeOffset + count($paginatedAssociations),
                'attribute' => $attributesResult->getOffset()
            ]
        ]);
    }

    private function formatSystemFields(array $fields, string $localeCode): array
    {
        if (count($fields) === 0) {
            return [];
        }

        $children = array_map(function (string $field) use ($localeCode): array {
            return [
                'code' => $field,
                'type' => 'property',
                'label' => $this->translator->trans(
                    sprintf('%s%s', self::FIELD_TRANSLATION_BASE, $field),
                    [],
                    null,
                    $localeCode
                ),
            ];
        }, $fields);

        return [[
            'code' => 'system',
            'label' => $this->translator->trans(self::SYSTEM_GROUP_TRANSLATION_KEY, [], null, $localeCode),
            'children' => $children,
        ]];
    }

    private function formatAssociationFields(array $fields, string $localeCode): array
    {
        if (empty($fields)) {
            return [];
        }

        $associationFields = array_map(static function (AssociationType $field) use ($localeCode): array {
            return [
                'code' => $field->getCode(),
                'type' => 'association_type',
                'label' => $field->getLabel($localeCode),
            ];
        }, $fields);

        return [[
            'code' => 'association_types',
            'label' => $this->translator->trans('pim_common.association_types'),
            'children' => $associationFields,
        ]];
    }

    /**
     * @param FlattenAttribute[] $flattenAttributes
     */
    private function formatAttributes(array $flattenAttributes): array
    {
        $results = [];
        foreach ($flattenAttributes as $flattenAttribute) {
            $groupCode = $flattenAttribute->getAttributeGroupCode();
            if (!array_key_exists($groupCode, $results)) {
                $results[$groupCode] = [
                    'code' => $groupCode,
                    'label' => $flattenAttribute->getAttributeGroupLabel(),
                    'children' => [],
                ];
            }

            $results[$groupCode]['children'][] = [
                'code' => $flattenAttribute->getCode(),
                'label' => $flattenAttribute->getLabel(),
                'type' => 'attribute',
            ];
        }

        return array_values($results);
    }
}
