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
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\SearchFlattenAttributesInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindSystemSourcesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetGroupedSourcesAction
{
    private const LIMIT_DEFAULT = 20;
    private const DEFAULT_LOCALE = 'en_US';

    private TranslatorInterface $translator;
    private FindSystemSourcesInterface $getSystemSources;
    private FindAssociationTypesInterface $findAssociationTypes;
    private FindFlattenAttributesInterface $findFlattenAttributes;

    public function __construct(
        TranslatorInterface $translator,
        FindSystemSourcesInterface $getSystemSources,
        FindAssociationTypesInterface $findAssociationTypes,
        FindFlattenAttributesInterface $findFlattenAttributes
    ) {
        $this->translator = $translator;
        $this->getSystemSources = $getSystemSources;
        $this->findAssociationTypes = $findAssociationTypes;
        $this->findFlattenAttributes = $findFlattenAttributes;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $options = $request->get('options', []);
        $search = $request->get('search');
        $limit = $options['limit'] ?? self::LIMIT_DEFAULT;
        $page = $options['page'];
        $offset = $page * $limit;

        $localeCode = $options['locale'] ?? self::DEFAULT_LOCALE;
        $attributeTypes = isset($options['attributeTypes']) ? explode(',', $options['attributeTypes']) : null;

        $paginatedFields = $this->getSystemSources->execute($localeCode, $limit, $offset, $search);
        $offset -= count($paginatedFields);
        $limit -= count($paginatedFields);

        $paginatedAssociations = $this->findAssociationTypes->execute($localeCode, $limit, $offset, $search);
        $offset -= count($paginatedAssociations);
        $limit -= count($paginatedAssociations);

        $paginatedAttributes = $this->findFlattenAttributes->execute(
            $localeCode,
            $limit,
            $attributeTypes,
            $offset,
            $search
        );

        return new JsonResponse(array_merge(
            $this->formatSystemFields($paginatedFields, $localeCode),
            $this->formatAssociationFields($paginatedAssociations, $localeCode),
            $this->formatAttributes($paginatedAttributes)
        ));
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
                'label' => $this->translator->trans(sprintf('pim_common.%s', $field), [], null, $localeCode),
            ];
        }, $fields);

        return [[
            'code' => 'system',
            'label' => $this->translator->trans('System', [], null, $localeCode),
            'children' => $children,
        ]];
    }

    private function formatAssociationFields(array $fields, string $localeCode): array
    {
        if (empty($fields)) {
            return [];
        }

        $associationFields = array_map(function (AssociationType $field) use ($localeCode): array {
            return [
                'code' => $field->getCode(),
                'type' => 'association_type',
                'label' => $field->getLabel($localeCode),
            ];
        }, $fields);

        return [[
            'code' => 'associations',
            'label' => $this->translator->trans('pim_common.association_types'),
            'children' => $associationFields,
        ]];
    }

    /**
     * @param FlattenAttribute[] $groupedAttributes
     */
    private function formatAttributes(array $groupedAttributes): array
    {
        $results = [];
        foreach ($groupedAttributes as $attribute) {
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
