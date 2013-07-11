<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

/**
 * @ORM\Table(name="oro_config_log")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ConfigLog
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigLog';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=8)
     */
    protected $username;

    /**
     * @var ConfigEntity
     * @ORM\ManyToOne(targetEntity="ConfigEntity", inversedBy="diffs")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var \DateTime
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var array
     * @ORM\Column(type="text")
     */
    protected $diff;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ConfigLog[] $configs
     * @return $this
     */
    public function setDiff(array $configs)
    {
        $this->diff = serialize($configs);

        return $this;
    }

    /**
     * @return EntityConfig[]
     */
    public function getDiff()
    {
        return unserialize($this->diff);
    }

    /**
     * @param \DateTime $loggedAt
     * @return $this
     */
    public function setLoggedAt($loggedAt)
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param ConfigEntity $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return ConfigEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->loggedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
