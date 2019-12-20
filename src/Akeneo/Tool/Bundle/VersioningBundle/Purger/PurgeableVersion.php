<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

class PurgeableVersion
{
    /** @var int */
    private $id;
    /** @var int */
    private $version;
    /** @var int */
    private $resourceId;
    /** @var string */
    private $resourceName;

    private function __construct(int $id, int $version, int $resourceId, string $resourceName)
    {
        $this->id = $id;
        $this->version = $version;
        $this->resourceId = $resourceId;
        $this->resourceName = $resourceName;
    }

    public static function create(int $id, int $version, int $resourceId, string $resourceName)
    {
        return new self($id, $version, $resourceId, $resourceName);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }
}
