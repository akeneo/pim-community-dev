<?php

namespace Oro\Bundle\EntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Table(name="oro_entity_audit_commit")
 * @ORM\Entity
 */
class AuditCommit
{
    const ENTITY_NAME = 'OroEntityBundle:AuditCommit';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var AuditDiff[]|PersistentCollection
     * @ORM\OneToMany(targetEntity="AuditDiff", mappedBy="commit", cascade={"all"})
     */
    protected $diffs;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $logged;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param PersistentCollection|AuditDiff[] $diffs
     * @return $this
     */
    public function setDiffs($diffs)
    {
        $this->diffs = $diffs;

        return $this;
    }

    /**
     * @param AuditDiff $diff
     * @return $this
     */
    public function addDiff(AuditDiff $diff)
    {
        if (!$this->diffs->contains($diff)) {
            $this->diffs->add($diff);
        }

        return $this;
    }

    /**
     * @param AuditDiff $diff
     * @return $this
     */
    public function removeDiff(AuditDiff $diff)
    {
        if ($this->diffs->contains($diff)) {
            $this->diffs->remove($diff);
        }

        return $this;
    }

    /**
     * @return PersistentCollection|AuditDiff[]
     */
    public function getDiffs()
    {
        return $this->diffs;
    }

    /**
     * @param \DateTime $logged
     * @return $this
     */
    public function setLogged($logged)
    {
        $this->logged = $logged;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLogged()
    {
        return $this->logged;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
