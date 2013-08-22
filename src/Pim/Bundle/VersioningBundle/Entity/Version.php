<?php

namespace Pim\Bundle\VersioningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\UserBundle\Entity\User;
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
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

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
     *
     * @param string  $resourceName
     * @param string  $resourceId
     * @param integer $numVersion
     * @param array   $data
     * @param User    $user
     */
    public function __construct($resourceName, $resourceId, $numVersion, $data, User $user)
    {
        $this->resourceName  = $resourceName;
        $this->resourceId    = $resourceId;
        $this->versionedData = $data;
        $this->version       = $numVersion;
        $this->user          = $user;
        $this->snapshotDate  = new \DateTime("now");
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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