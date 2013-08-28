<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="oro_entity_config_log")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ConfigLog
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigLog';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var UserInterface
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var ConfigLogDiff[]
     * @ORM\OneToMany(targetEntity="ConfigLogDiff", mappedBy="log", cascade={"all"})
     */
    protected $diffs;

    /**
     * @var \DateTime
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    public function __construct()
    {
        $this->diffs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param UserInterface $user
     * @return $this
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param ConfigLogDiff[] $diffs
     * @return $this
     */
    public function setDiffs($diffs)
    {
        $this->diffs = $diffs;

        return $this;
    }

    /**
     * @param ConfigLogDiff $diff
     * @return $this
     */
    public function addDiff(ConfigLogDiff $diff)
    {
        if (!$this->diffs->contains($diff)) {
            $diff->setLog($this);
            $this->diffs->add($diff);
        }

        return $this;
    }

    /**
     * @return ConfigLogDiff[]|ArrayCollection
     */
    public function getDiffs()
    {
        return $this->diffs;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->loggedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
