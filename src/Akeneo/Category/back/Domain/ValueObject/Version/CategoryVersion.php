<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Version;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-type Snapshot array{
 *     code: string,
 *     parent: string,
 *     updated: string
 * }&array<string, string>
 */
class CategoryVersion
{
    public const CATEGORY_VERSION_RESOURCE_NAME = "Akeneo\Category\Infrastructure\Component\Model\Category";

    /**
     * snapshot example : [
     *    code: string,
     *    parent: string,
     *    updated: string,
     *    label-en_US: string,
     *    label-fr_FR: string
     * ]
     * @param Snapshot $snapshot
     */
    private function __construct(
        private readonly ?string $resourceId,
        private readonly array $snapshot,
    ) {
    }

    /**
     * @param string|null $resourceId The category id
     * @param Snapshot $snapshot
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
     * @return Snapshot
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
