<?php

namespace Akeneo\Tool\Component\Versioning\Model;

/**
 * Resource version interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: use constructor and remove setters to make it immutable
 */
interface VersionInterface
{
    /**
     * Get id
     */
    public function getId(): int;

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id): Version;

    /**
     * Get author
     */
    public function getAuthor(): string;

    /**
     * Get resource id
     */
    public function getResourceId(): string;

    /**
     * Get resource name
     */
    public function getResourceName(): string;

    /**
     * Get version
     */
    public function getVersion(): int;

    /**
     * Set version
     *
     * @param int $version
     */
    public function setVersion(int $version): \Akeneo\Tool\Component\Versioning\Model\VersionInterface;

    /**
     * Get snapshot
     */
    public function getSnapshot(): array;

    /**
     * Set snapshot
     *
     * @param array $snapshot
     */
    public function setSnapshot(array $snapshot): \Akeneo\Tool\Component\Versioning\Model\VersionInterface;

    /**
     * Get changeset
     */
    public function getChangeset(): array;

    /**
     * Set changeset
     *
     * @param array $changeset
     */
    public function setChangeset(array $changeset): \Akeneo\Tool\Component\Versioning\Model\VersionInterface;

    /**
     * Get context
     */
    public function getContext(): string;

    public function getLoggedAt(): \DateTime;

    public function isPending(): bool;
}
