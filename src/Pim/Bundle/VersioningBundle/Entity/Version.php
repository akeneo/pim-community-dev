<?php

namespace Pim\Bundle\VersioningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\VersioningBundle\Model\Versionable;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
     * @Column(type="integer") */
    protected $version;

    /**
     * @ORM\Column(type="datetime")
     */
    private $snapshotDate;

    /**
     * Constructor
     * @param Versionable $resource
     */
    public function __construct(Versionable $resource)
    {
        $this->resourceName  = get_class($resource);
        $this->resourceId    = $resource->getId();
        $this->versionedData = $resource->getVersionedData();
        $this->version       = 1;//$resource->getCurrentVersion();
        $this->snapshotDate  = new \DateTime("now");
    }
}