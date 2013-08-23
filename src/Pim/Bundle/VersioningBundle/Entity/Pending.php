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
 * @ORM\Table(name="pim_versioning_pending")
 */
class Pending
{
    /**
     * @var integer
     */
    const STATUS_PENDING = 1;

    /**
     * @var integer
     */
    const STATUS_IN_PROGRESS = 1;

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
     * @ORM\Column(type="string")
     */
    protected $resourceName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $resourceId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status = self::STATUS_PENDING;

    /**
     * Constructor
     *
     * @param string  $resourceName
     * @param string  $resourceId
     * @param string  $username
     */
    public function __construct($resourceName, $resourceId, $username)
    {
        $this->resourceName = $resourceName;
        $this->resourceId   = $resourceId;
        $this->username     = $username;
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

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer
     *
     * @return Pending
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
