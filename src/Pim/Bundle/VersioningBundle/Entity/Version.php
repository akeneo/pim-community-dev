<?php

namespace Pim\Bundle\VersioningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\VersioningBundle\Entity\Repository\VersionRepository")
 * @ORM\Table(
 *      name="pim_versioning_version",
 *      indexes={
 *          @ORM\Index(name="resource_name_resource_id_idx", columns={"resource_name", "resource_id"})
 *      }
 * )
 */
class Version
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $author
     *
     * @ORM\Column(type="string")
     */
    protected $author;

    /**
     * @ORM\Column(name="resource_name", type="string")
     */
    protected $resourceName;

    /**
     * @ORM\Column(name="resource_id", type="string", length=24)
     */
    protected $resourceId;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $snapshot;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $changeset;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $context;

    /**
     * @ORM\Column(type="integer") */
    protected $version;

    /**
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @ORM\Column(name="pending", type="boolean")
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
        if (!empty($changeset)) {
            $this->pending = false;
        }

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
}
