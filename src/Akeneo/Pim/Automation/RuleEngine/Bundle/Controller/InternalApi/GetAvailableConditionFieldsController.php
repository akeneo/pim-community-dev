<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetGroupedAttributes;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class GetAvailableConditionFieldsController
{
    private const LIMIT_DEFAULT = 20;
    private const FIELD_TRANSLATION_BASE = 'pimee_catalog_rule.condition.fields.';
    private const SYSTEM_GROUP_TRANSLATION_KEY = 'pimee_catalog_rule.condition.field_groups.system';

    // Add here the fields handled by the rule conditions.
    // Be sure that the associated UI component exists to display it correctly.
    private const AVAILABLE_SYSTEM_FIELDS = [
        'family',
    ];
    private const AVAILABLE_FIELD_ATTRIBUTE_TYPES = [
        'pim_catalog_multiselect',
    ];

    /** @var GetGroupedAttributes */
    private $getGroupedAttributes;

    /** @var UserContext */
    protected $userContext;

    /** @var TranslatorInterface */
    protected $translator;

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
        $page = $options['page'] ?? 1;
        $offset = ($page - 1) * $limit;

        $fields = $this->filterSystemFieldByText(static::AVAILABLE_SYSTEM_FIELDS, $search);
        $paginatedFields = array_slice($fields, $offset, $limit);

        $paginatedAttributes = [];
        if ($limit > count($paginatedFields)) {
            $offset -= count($fields);
            $limit -= count($paginatedFields);
            $localeCode = $this->userContext->getCurrentLocaleCode();

            $paginatedAttributes = $this->getGroupedAttributes->getForAttributeTypes(
                static::AVAILABLE_FIELD_ATTRIBUTE_TYPES,
                $localeCode,
                $limit,
                $offset,
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
                'id' => $field,
                'text' => $this->translator->trans(
                    sprintf('%s%s', static::FIELD_TRANSLATION_BASE, $field),
                    [],
                    null,
                    null !== $uiLocale ? $uiLocale->getCode() : null
                ),
            ];
        }, $fields);

        return [[
            'id' => 'system',
            'text' => $this->translator->trans(static::SYSTEM_GROUP_TRANSLATION_KEY),
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
                    'id' => $groupCode,
                    'text' => $attribute['group_label'],
                    'children' => [],
                ];
            }

            $results[$groupCode]['children'][] = [
                'id' => $attribute['code'],
                'text' => $attribute['label'],
            ];
        }

        return array_values($results);
    }
}
