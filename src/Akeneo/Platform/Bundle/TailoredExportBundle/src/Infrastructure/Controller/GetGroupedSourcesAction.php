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

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Platform\TailoredExport\Domain\Query\GetGroupedAttributes;
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

    private GetGroupedAttributes $getGroupedAttributes;
    protected UserContext $userContext;
    protected TranslatorInterface $translator;

    public function __construct(
        GetGroupedAttributes $getGroupedAttributes,
        UserContext $userContext,
        TranslatorInterface $translator
    ) {
        $this->getGroupedAttributes = $getGroupedAttributes;
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $options = $request->get('options', []);
        $search = $request->get('search');
        $limit = $options['limit'] ?? static::LIMIT_DEFAULT;
        $page = $options['page'];
        $offset = $page * $limit;
        $localeCode = $options['locale'];
        $attributeTypes = isset($options['attributeTypes']) ? explode(',', $options['attributeTypes']) : null;

        $fields = [
            'categories',
            'enabled',
            'family',
            'parent',
            'groups',
            'family_variant',
            // 'identifier',
            // 'created',
            // 'updated',
            // 'entity_type',
            // 'completeness',
            // 'associations',
            // 'quantified_associations', //nope
        ];

        $filteredFields = $this->filterSystemFieldByText($fields, $search);
        $paginatedFields = array_slice($filteredFields, $offset, $limit);

        $paginatedAttributes = [];
        if ($limit > count($paginatedFields)) {
            $offset -= count($filteredFields);
            $limit -= count($paginatedFields);

            $paginatedAttributes = $this->getGroupedAttributes->findAttributes(
                $localeCode,
                $limit,
                $offset,
                $attributeTypes,
                $search
            );
        }

        return new JsonResponse(array_merge(
            $this->formatSystemFields($paginatedFields),
            $this->formatAttributes($paginatedAttributes)
        ));
    }

    private function filterSystemFieldByText(array $fields, ?string $search): array
    {
        if (null === $search || '' === trim($search)) {
            return $fields;
        }

        return array_filter($fields, function (string $field) use ($search): bool {
            return strpos(strtolower($field), strtolower($search)) !== false;
        });
    }

    private function formatSystemFields(array $fields): array
    {
        if (count($fields) === 0) {
            return [];
        }

        $uiLocale = $this->userContext->getUiLocale();
        $children = array_map(function (string $field) use ($uiLocale): array {
            return [
                'code' => $field,
                'type' => 'property',
                'label' => $this->translator->trans(
                    sprintf('%s%s', static::FIELD_TRANSLATION_BASE, $field),
                    [],
                    null,
                    null !== $uiLocale ? $uiLocale->getCode() : null
                ),
            ];
        }, $fields);

        return [[
            'code' => 'system',
            'label' => $this->translator->trans(static::SYSTEM_GROUP_TRANSLATION_KEY),
            'children' => $children,
        ]];
    }

    private function formatAttributes(array $attributes): array
    {
        $results = [];
        foreach ($attributes as $attribute) {
            $groupCode = $attribute['group_code'];
            if (!array_key_exists($groupCode, $results)) {
                $results[$groupCode] = [
                    'code' => $groupCode,
                    'label' => $attribute['group_label'],
                    'children' => [],
                ];
            }

            $results[$groupCode]['children'][] = [
                'code' => $attribute['code'],
                'label' => $attribute['label'],
                'type' => 'attribute',
            ];
        }

        return array_values($results);
    }
}
