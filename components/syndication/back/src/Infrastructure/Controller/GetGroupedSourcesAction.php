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

namespace Akeneo\Platform\Syndication\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\FindAssociationTypesInterface;
use Akeneo\Platform\Syndication\Domain\Query\Attribute\Attribute;
use Akeneo\Platform\Syndication\Domain\Query\Attribute\FindViewableAttributesInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindStaticSourcesInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindSystemSourcesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetGroupedSourcesAction
{
    private const LIMIT_DEFAULT = 25;
    private const FIELD_TRANSLATION_BASE = 'pim_common.';
    private const STATIC_TRANSLATION_BASE = 'akeneo.syndication.data_mapping_details.sources.static.';
    private const SYSTEM_GROUP_TRANSLATION_KEY = 'System';
    private const STATIC_GROUP_TRANSLATION_KEY = 'akeneo.syndication.data_mapping_details.sources.static.title';
    private const DEFAULT_LOCALE = 'en_US';

    private TranslatorInterface $translator;
    private FindStaticSourcesInterface $getStaticSources;
    private FindSystemSourcesInterface $getSystemSources;
    private FindAssociationTypesInterface $findAssociationTypes;
    private FindViewableAttributesInterface $findViewableAttributes;

    public function __construct(
        TranslatorInterface $translator,
        FindStaticSourcesInterface $getStaticSources,
        FindSystemSourcesInterface $getSystemSources,
        FindAssociationTypesInterface $findAssociationTypes,
        FindViewableAttributesInterface $findViewableAttributes
    ) {
        $this->translator = $translator;
        $this->getStaticSources = $getStaticSources;
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
        $limit = (int) ($options['limit'] ?? self::LIMIT_DEFAULT);
        $systemOffset = (int) $options['offset']['system'];
        $staticOffset = (int) $options['offset']['static'];
        $associationTypeOffset = (int) $options['offset']['association_type'];
        $attributeOffset = (int) $options['offset']['attribute'];
        $type = $options['type'];

        $localeCode = $options['locale'] ?? self::DEFAULT_LOCALE;

        $paginatedStaticFields = $this->getStaticSources->execute($localeCode, $limit, $staticOffset, $search, $type);
        $limit -= count($paginatedStaticFields);
        $paginatedSystemFields = $this->getSystemSources->execute($localeCode, $limit, $systemOffset, $search, $type);
        $limit -= count($paginatedSystemFields);

        //TODO: do this properly
        if ('string' === $type) {
            $paginatedAssociations = $this->findAssociationTypes->execute($localeCode, $limit, $associationTypeOffset, $search);
            $limit -= count($paginatedAssociations);
        } else {
            $paginatedAssociations = [];
        }

        $attributesResult = $this->findViewableAttributes->execute(
            $localeCode,
            $limit,
            $attributeOffset,
            $search,
            $type
        );

        return new JsonResponse([
            'results' => array_merge(
                $this->formatStaticFields($paginatedStaticFields, $localeCode),
                $this->formatSystemFields($paginatedSystemFields, $localeCode),
                $this->formatAssociationFields($paginatedAssociations, $localeCode),
                $this->formatAttributes($attributesResult->getAttributes())
            ),
            'offset' => [
                'static'           => $staticOffset + count($paginatedStaticFields),
                'system'           => $systemOffset + count($paginatedSystemFields),
                'association_type' => $associationTypeOffset + count($paginatedAssociations),
                'attribute'        => $attributesResult->getOffset()
            ]
        ]);
    }

    private function formatStaticFields(array $fields, string $localeCode): array
    {
        if (empty($fields)) {
            return [];
        }

        $children = array_map(fn (string $field): array => [
            'code' => $field,
            'type' => 'static',
            'label' => $this->translator->trans(
                sprintf('%s%s.title', self::STATIC_TRANSLATION_BASE, $field),
                [],
                null,
                $localeCode
            ),
        ], $fields);

        return [[
            'code' => 'static',
            'label' => $this->translator->trans(self::STATIC_GROUP_TRANSLATION_KEY, [], null, $localeCode),
            'children' => $children,
        ]];
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
                $localeCode
            ),
        ], $fields);

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

        $associationFields = array_map(static fn (AssociationType $field): array => [
            'code' => $field->getCode(),
            'type' => 'association_type',
            'label' => $field->getLabel($localeCode),
        ], $fields);

        return [[
            'code' => 'association_types',
            'label' => $this->translator->trans('pim_common.association_types'),
            'children' => $associationFields,
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
