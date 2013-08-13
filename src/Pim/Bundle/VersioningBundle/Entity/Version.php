<?php

namespace Pim\Bundle\VersioningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(name="pim_versioning_version")
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
     * @ORM\Column(type="string")
     */
    protected $resourceName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $resourceId;

    /**
     * @ORM\Column(type="array")
     */
    protected $versionedData;

    /**
     * @ORM\Column(type="integer") */
    protected $version;

    /**
     * @ORM\Column(type="datetime")
     */
    private $snapshotDate;

    /**
     * Constructor
     * @param VersionableInterface $resource
     */
    public function __construct(VersionableInterface $resource)
    {
        $this->resourceName  = get_class($resource);
        $this->resourceId    = $resource->getId();
        $this->versionedData = $resource->getVersionedData();
        $this->version       = $resource->getVersion();
        $this->snapshotDate  = new \DateTime("now");
    }

    /**
     * @return integer
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @return array
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getVersionedData()
    {
        return $this->versionedData;
    }
}