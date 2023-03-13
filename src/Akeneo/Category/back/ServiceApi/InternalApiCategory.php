<?php

declare(strict_types=1);

namespace Akeneo\Category\ServiceApi;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type NormalizedValue from ValueCollection
 */
class InternalApiCategory
{
    /**
     * @return array{
     *     id: int|null,
     *     parent: int|null,
     *     root_id: int | null,
     *     template_uuid: string | null,
     *     properties: array{
     *       code: string,
     *       labels: array<string, string>|null
     *     },
     *     attributes: array<string, array<string, mixed>> | null,
     *     permissions: array<string, array<int>>|null,
     *     isRoot: boolean,
     *     root: array{
     *       id: int|null,
     *       parent: int|null,
     *       root_id: int | null,
     *       template_uuid: string | null,
     *       properties: array{
     *         code: string,
     *         labels: array<string, string>|null
     *       },
     *       attributes: array<string, array<string, mixed>> | null,
     *       permissions: array<string, array<int>>|null,
     *      }|null
     * }
     */
    public static function normalize(Category $category, ?Category $root): array
    {
        $normalizedCategory = $category->normalize();
        $responseLabels = $normalizedCategory['properties']['labels'];
        $normalizedCategory['properties']['labels'] = empty($responseLabels) ? (object) [] : $responseLabels;
        $normalizedCategory['isRoot'] = $category->isRoot();
        $normalizedCategory['root'] = $root?->normalize();
        if ($root) {
            $responseRootLabels = $normalizedCategory['root']['properties']['labels'];
            $normalizedCategory['root']['properties']['labels'] = empty($responseRootLabels) ? (object) [] : $responseRootLabels;
        }

        return $normalizedCategory;
    }
}
