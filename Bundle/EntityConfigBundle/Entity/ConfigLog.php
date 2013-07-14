<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\UserBundle\Entity\User;

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
     * @var User $user
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var ConfigEntity
     * @ORM\ManyToOne(targetEntity="ConfigEntity", inversedBy="diffs")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var ConfigField
     * @ORM\ManyToOne(targetEntity="ConfigField", inversedBy="diffs")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id")
     * })
     */
    protected $field;

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
     * @param \Oro\Bundle\EntityConfigBundle\Entity\ConfigField $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return \Oro\Bundle\EntityConfigBundle\Entity\ConfigField
     */
    public function getField()
    {
        return $this->field;
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
     * @param User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
