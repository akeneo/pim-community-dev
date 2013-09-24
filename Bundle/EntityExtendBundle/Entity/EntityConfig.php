<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_extend_config")
 * @ORM\Entity(repositoryClass="Oro\Bundle\EntityExtendBundle\Entity\Repository\EntityConfigRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class EntityConfig
{
    const ENTITY_NAME = 'OroEntityExtendBundle:EntityConfig';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $config;

    /**
     * @var \DateTime
     * @ORM\Column(name="create_at", type="datetime")
     */
    protected $createAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $active;

    public function __construct($config, $active)
    {
        $this->active = $active;
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \DateTime $createAt
     * @return $this
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
