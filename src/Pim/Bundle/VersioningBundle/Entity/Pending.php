<?php

namespace Pim\Bundle\VersioningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Pending entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="pim_versioning_pending",
 *      indexes={
 *          @ORM\Index(name="resource_name_resource_id_idx", columns={"resource_name", "resource_id"})
 *      }
 * )
 */
class Pending
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
    protected $username;

    /**
     * @ORM\Column(name="resource_name", type="string")
     */
    protected $resourceName;

    /**
     * @ORM\Column(name="resource_id", type="string", length=24)
     */
    protected $resourceId;

    /**
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * Constructor
     *
     * @param string $resourceName
     * @param string $resourceId
     * @param string $username
     */
    public function __construct($resourceName, $resourceId, $username)
    {
        $this->resourceName = $resourceName;
        $this->resourceId   = $resourceId;
        $this->username     = $username;
        $this->loggedAt     = new \DateTime("now");
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUsername()
    {
        return $this->username;
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
}
