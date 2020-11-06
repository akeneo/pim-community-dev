<?php

namespace Akeneo\Tool\Component\Versioning\Model;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version implements VersionInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var string
     */
    protected $resourceId;

    /**
     * @var array
     */
    protected $snapshot;

    /**
     * @var array
     */
    protected $changeset;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var \DateTime
     */
    protected $loggedAt;

    /**
     * @var bool
     */
    protected $pending;

    /**
     * Constructor
     *
     * @param string      $resourceName
     * @param string      $resourceId
     * @param string      $author
     * @param string|null $context
     */
    public function __construct(string $resourceName, string $resourceId, string $author, ?string $context = null)
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
        $this->author = $author;
        $this->context = $context;
        $this->loggedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->pending = true;
    }

    /**
     * Get id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id): \Akeneo\Tool\Component\Versioning\Model\Version
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get author
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get resource id
     */
    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * Get resource name
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * Get version
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param int $version
     */
    public function setVersion(int $version): VersionInterface
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get snapshot
     */
    public function getSnapshot(): array
    {
        return $this->snapshot;
    }

    /**
     * Set snapshot
     *
     * @param array $snapshot
     */
    public function setSnapshot(array $snapshot): VersionInterface
    {
        if (!empty($snapshot)) {
            $this->pending = false;
        }

        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Get changeset
     */
    public function getChangeset(): array
    {
        return $this->changeset;
    }

    /**
     * Set changeset
     *
     * @param array $changeset
     */
    public function setChangeset(array $changeset): VersionInterface
    {
        $this->changeset = $changeset;

        return $this;
    }

    /**
     * Get context
     */
    public function getContext(): string
    {
        return $this->context;
    }

    public function getLoggedAt(): \DateTime
    {
        return $this->loggedAt;
    }

    public function isPending(): bool
    {
        return $this->pending;
    }
}
