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

    /**
     * @param array<string, mixed> $categoryChangeset
     */
    public function create(Category $category, array $categoryChangeset): ?CategoryVersion
    {
        if (empty($categoryChangeset)) {
            return null;
        }

        $categoryId = $category->getId() ? (string) $category->getId()->getValue() : null;

        $snapshot = $this->buildSnapshot($category, $categoryChangeset['updated']['new']);

        $changeset = $this->buildChangeset($category, $categoryChangeset);

        return CategoryVersion::fromBuilder(
            resourceId: $categoryId,
            snapshot: $snapshot,
            changeset: $changeset,
        );
    }

    /**
     * @return Snapshot
     */
    public function buildSnapshot(Category $category, string $updatedAt): array
    {
        if (null !== $category->getParentId()) {
            $parent = $this->getCategory->byId($category->getParentId()->getValue());
        }
        $parent = !empty($parent) ? (string) $parent->getCode() : '';

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
            'parent' => $parent,
            'updated' => $updatedAt,
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

    /**
     * @param array<string, mixed> $categoryChangeset
     *
     * @return array<string, array{old: string, new: string}>
     */
    private function buildChangeset(Category $category, array $categoryChangeset): array
    {
        $changeset = [];
        $changeset['updated'] = $categoryChangeset['updated'];
        foreach ($categoryChangeset as $key => $changes) {
            if ($key === 'labels') {
                foreach ($changes as $locale => $value) {
                    $changeset["label-$locale"] = $value;
                }
            }
        }

        return $changeset;
    }
}
