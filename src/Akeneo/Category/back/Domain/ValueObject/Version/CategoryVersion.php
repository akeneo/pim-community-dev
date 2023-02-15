<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Version;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-type Snapshot array{
 *     code: string,
 *     parent: string|null,
 *     updated: string
 * }&array<string, string>
 */
class CategoryVersion
{
    public const CATEGORY_VERSION_RESOURCE_NAME = "Akeneo\Category\Infrastructure\Component\Model\Category";

    /**
     * snapshot full example : [
     *    'code': 'print',
     *    'parent': 'categories',
     *    'updated': '2023-01-17T13:03:43+00:00',
     *    'label-en_US': 'print',
     *    'label-fr_FR': 'impression',
     *    'view_permission': 'All',
     *    'edit_permission': 'Redactor, Manager'
     *    'own_permission': 'Redactor, Manager'
     * ].
     *
     * @phpstan-param Snapshot $snapshot
     */
    private function __construct(
        private readonly ?string $resourceId,
        private readonly array $snapshot,
    ) {
    }

    /**
     * @param string|null $resourceId The category id
     *
     * @phpstan-param Snapshot $snapshot
     */
    public static function fromBuilder(?string $resourceId, array $snapshot): self
    {
        return new self($resourceId, $snapshot);
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    /**
     * @phpstan-return Snapshot
     */
    public function getSnapshot(): array
    {
        return $this->snapshot;
    }

    public function getResourceName(): string
    {
        return self::CATEGORY_VERSION_RESOURCE_NAME;
    }
}
