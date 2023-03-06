<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetGroupedAttributes;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ConditionItem array{'id': string, 'text': string}
 * @phpstan-type ConditionItemGroup array{'id': string, 'text': string, 'children': non-empty-list<ConditionItem>}
 * @phpstan-import-type AttributeDetails from GetGroupedAttributes
 */
final class GetAvailableConditionsController
{
    private const DEFAULT_LIMIT_PAGINATION = 20;
    private const FIELD_TRANSLATION_BASE = 'pim_catalog_identifier_generator.condition.fields.';
    private const SYSTEM_GROUP_TRANSLATION_KEY = 'pim_catalog_identifier_generator.condition.field_groups.system';

    public function __construct(
        private readonly GetGroupedAttributes $getGroupedAttributes,
        private readonly UserContext $userContext,
        private readonly TranslatorInterface $translator,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_manage')) {
            throw new AccessDeniedException();
        }

        $search = $request->query->getAlpha('search');
        $limit = $request->query->getInt('limit', self::DEFAULT_LIMIT_PAGINATION);
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;

        $fields = $request->get('systemFields', []);
        Assert::isArray($fields);
        Assert::allStringNotEmpty($fields);

        $filteredFields = $this->filterSystemFieldByText($fields, $search);
        $paginatedFields = \array_slice($filteredFields, $offset, $limit);

        $canListAttributes = $this->security->isGranted('pim_enrich_attribute_index');
        $paginatedAttributes = [];
        if ($limit > \count($paginatedFields) && $canListAttributes) {
            $offset -= \count($filteredFields);
            $limit -= \count($paginatedFields);
            $localeCode = $this->userContext->getCurrentLocaleCode();

            $paginatedAttributes = $this->getGroupedAttributes->findAttributes(
                $localeCode,
                $limit,
                $offset,
                [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::OPTION_MULTI_SELECT],
                $search
            );
        }

        return new JsonResponse(
            \array_merge(
                $this->formatSystemFields($paginatedFields),
                $this->formatAttributes($paginatedAttributes)
            )
        );
    }

    /**
     * @param string[] $fields
     * @param string|null $search
     *
     * @return string[]
     */
    private function filterSystemFieldByText(array $fields, ?string $search): array
    {
        if (null === $search || '' === \trim($search)) {
            return $fields;
        }

        return \array_filter(
            $fields,
            fn (string $field): bool =>
                \str_contains(\strtolower($field), \strtolower($search))
                || \str_contains(
                    \strtolower(
                        $this->translator->trans(
                            \sprintf('%s%s', static::FIELD_TRANSLATION_BASE, $field),
                            [],
                            null,
                            $this->userContext->getCurrentLocaleCode()
                        )
                    ),
                    \strtolower($search)
                )
        );
    }

    /**
     * @param string[] $fields
     *
     * @return list<ConditionItemGroup>
     */
    private function formatSystemFields(array $fields): array
    {
        if (\count($fields) === 0) {
            return [];
        }

        $children = \array_map(
            fn (string $field): array => [
                'id' => $field,
                'text' => $this->translator->trans(
                    \sprintf('%s%s', static::FIELD_TRANSLATION_BASE, $field),
                    [],
                    null,
                    $this->userContext->getCurrentLocaleCode()
                ),
            ],
            $fields
        );

        return [
            [
                'id' => 'system',
                'text' => $this->translator->trans(
                    static::SYSTEM_GROUP_TRANSLATION_KEY,
                    [],
                    null,
                    $this->userContext->getCurrentLocaleCode()
                ),
                'children' => $children,
            ],
        ];
    }

    /**
     * @param AttributeDetails[] $attributes
     *
     * @return list<ConditionItemGroup>
     */
    private function formatAttributes(array $attributes): array
    {
        $results = [];
        foreach ($attributes as $attribute) {
            $groupCode = $attribute['group_code'];
            if (!\array_key_exists($groupCode, $results)) {
                $results[$groupCode] = [
                    'id' => $groupCode,
                    'text' => $attribute['group_label'],
                    'children' => [],
                ];
            }

            $results[$groupCode]['children'][] = [
                'id' => $attribute['code'],
                'text' => $attribute['label'],
                'type' => $attribute['type'],
            ];
        }

        return \array_values($results);
    }
}
