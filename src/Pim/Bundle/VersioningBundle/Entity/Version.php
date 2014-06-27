<?php

namespace Pim\Bundle\VersioningBundle\Entity;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version
{
    /**
     * @var integer
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
     * @var integer
     */
    protected $version;

    /**
     * @var datetime
     */
    protected $loggedAt;

    /**
     * @var boolean
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
    public function __construct($resourceName, $resourceId, $author, $context = null)
    {
        $this->resourceName = $resourceName;
        $this->resourceId   = $resourceId;
        $this->author       = $author;
        $this->context      = $context;
        $this->loggedAt     = new \DateTime('now');
        $this->pending      = true;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get resource id
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param integer $version
     *
     * @return Version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get snapshot
     *
     * @return array
     */
    public function getSnapshot()
    {
        return $this->snapshot;
    }

    /**
     * Set snapshot
     *
     * @param array $snapshot
     *
     * @return Version
     */
    public function setSnapshot(array $snapshot)
    {
        if (!empty($snapshot)) {
            $this->pending = false;
        }

        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Get changeset
     *
     * @return array
     */
    public function getChangeset()
    {
        return $this->changeset;
    }

    /**
     * Set changeset
     *
     * @param array $changeset
     *
     * @return Version
     */
    public function setChangeset(array $changeset)
    {
        $this->changeset = $changeset;

        return $this;
    }

    /**
     * Get context
     *
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return datetime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * @return boolean
     */
    public function isPending()
    {
        return $this->pending;
    }
}
