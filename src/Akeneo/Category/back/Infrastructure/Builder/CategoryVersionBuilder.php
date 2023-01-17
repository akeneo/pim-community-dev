<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Version\CategoryVersion;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryVersionBuilder
{
    public function __construct(private readonly GetCategoryInterface $getCategory)
    {
    }

    public function create(Category $category): CategoryVersion
    {
        $categorySnapshot = $category->normalize();

        $categoryId = (string) $categorySnapshot['id'] ?? null;
        $categorySnapshot['code'] = $categorySnapshot['properties']['code'];
        if (!empty($categorySnapshot['parent'])) {
            $parent = $this->getCategory->byId($categorySnapshot['parent']);
        }
        $categorySnapshot['parent'] = !empty($parent) ? (string) $parent->getCode() : '';
        $categorySnapshot['updated'] = $category->getUpdated()?->format('c') ?? '';

        foreach ($categorySnapshot['properties']['labels'] as $locale => $label) {
            $key = "label-$locale";
            $categorySnapshot[$key] = $label;
        }

        unset(
            $categorySnapshot['id'],
            $categorySnapshot['root_id'],
            $categorySnapshot['template_uuid'],
            $categorySnapshot['properties'],
            $categorySnapshot['attributes'],
            $categorySnapshot['permissions'],
        );

        return CategoryVersion::fromBuilder(
            resourceId: $categoryId,
            snapshot: $categorySnapshot,
        );
    }
}
