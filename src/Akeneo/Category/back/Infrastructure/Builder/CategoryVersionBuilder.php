<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Version\CategoryVersion;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Snapshot from CategoryVersion
 * @phpstan-import-type Permission from PermissionCollection
 */
class CategoryVersionBuilder
{
    public function __construct(private readonly GetCategoryInterface $getCategory)
    {
    }

    public function create(Category $category): CategoryVersion
    {
        $categoryId = $category->getId() ? (string) $category->getId()->getValue() : null;

        $categorySnapshot = $this->buildSnapshot($category);

        return CategoryVersion::fromBuilder(
            resourceId: $categoryId,
            snapshot: $categorySnapshot,
        );
    }

    /**
     * @return Snapshot
     */
    public function buildSnapshot(Category $category): array
    {
        $snapshotParent = null;

        if (!$category->isRoot() && null !== $category->getParentId()) {
            $parent = $this->getCategory->byId($category->getParentId()->getValue());
            $snapshotParent = (string) $parent->getCode();
        }

        if (!$category->isRoot() && empty($snapshotParent) && null !== $category->getRootId()) {
            $root = $this->getCategory->byId($category->getRootId()->getValue());
            $snapshotParent = (string) $root->getCode();
        }

        $snapshotLabels = [];
        foreach ($category->getLabels()?->normalize() as $locale => $label) {
            $key = "label-$locale";
            $snapshotLabels[$key] = $label;
        }

        $snapshotPermissions = [];
        if (null !== $category->getPermissions()) {
            $snapshotPermissions['view_permission'] = $this->buildSnapshotPermission($category->getPermissions()->getViewUserGroups());
            $snapshotPermissions['edit_permission'] = $this->buildSnapshotPermission($category->getPermissions()->getEditUserGroups());
            $snapshotPermissions['own_permission'] = $this->buildSnapshotPermission($category->getPermissions()->getOwnUserGroups());
        }

        $snapshot = [
            'code' => (string) $category->getCode(),
            'parent' => $snapshotParent,
            'updated' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('c') ?? '',
        ];

        return array_merge($snapshot, $snapshotLabels, $snapshotPermissions);
    }

    /**
     * @param array<Permission> $permissionTypeItems
     *
     * @return string the list of the permissions label
     */
    private function buildSnapshotPermission(array $permissionTypeItems): string
    {
        $permissions = [];
        foreach ($permissionTypeItems as $item) {
            $permissions[] = $item['label'];
        }

        return implode(',', $permissions);
    }
}
