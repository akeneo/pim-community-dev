<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

final class PurgeableVersionList implements \Countable
{
    /** @var int[] */
    private $versionIds;

    /** @var string */
    private $resourceName;

    public function __construct(string $resourceName, array $ids)
    {
        $this->resourceName = $resourceName;
        $this->versionIds = $ids;
    }

    public function getVersionIds(): array
    {
        return $this->versionIds;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function count(): int
    {
        return count($this->versionIds);
    }

    public function remove(array $versionIds): self
    {
        if (empty($versionIds)) {
            return $this;
        }

        $versionIds = array_values(array_diff($this->versionIds, $versionIds));

        return new self($this->resourceName, $versionIds);
    }

    public function keep(array $versionIds): self
    {
        if (!empty($versionIds)) {
            $versionIds = array_values(array_intersect($this->versionIds, $versionIds));
        }

        return new self($this->resourceName, $versionIds);
    }
}
