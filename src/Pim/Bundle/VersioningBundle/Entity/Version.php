<?php

namespace Pim\Bundle\VersioningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\UserBundle\Entity\User;

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
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="resource_name", type="string")
     */
    protected $resourceName;

    /**
     * @ORM\Column(name="resource_id", type="integer")
     */
    protected $resourceId;

    /**
     * @ORM\Column(type="array")
     */
    protected $data;

    /**
     * @ORM\Column(type="integer") */
    protected $version;

    /**
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

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
        $this->resourceName = $resourceName;
        $this->resourceId   = $resourceId;
        $this->data         = $data;
        $this->version      = $numVersion;
        $this->user         = $user;
        $this->loggedAt     = new \DateTime("now");
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
    public function getData()
    {
        return $this->data;
    }
}
