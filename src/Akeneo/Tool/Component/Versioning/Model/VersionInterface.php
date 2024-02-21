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
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Version
     */
    public function setId($id);

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor();

    /**
     * Get resource id
     *
     * @return string
     */
    public function getResourceId();

    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName();

    /**
     * Get version
     *
     * @return int
     */
    public function getVersion();

    /**
     * Set version
     *
     * @param int $version
     *
     * @return VersionInterface
     */
    public function setVersion($version);

    /**
     * Get snapshot
     *
     * @return array
     */
    public function getSnapshot();

    /**
     * Set snapshot
     *
     * @param array $snapshot
     *
     * @return VersionInterface
     */
    public function setSnapshot(array $snapshot);

    /**
     * Get changeset
     *
     * @return array
     */
    public function getChangeset();

    /**
     * Set changeset
     *
     * @param array $changeset
     *
     * @return VersionInterface
     */
    public function setChangeset(array $changeset);

    /**
     * Get context
     *
     * @return string
     */
    public function getContext();

    /**
     * @return \DateTime
     */
    public function getLoggedAt();

    /**
     * @return bool
     */
    public function isPending();
}
