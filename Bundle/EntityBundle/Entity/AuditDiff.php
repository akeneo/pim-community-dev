<?php

namespace Oro\Bundle\EntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_entity_audit_diff")
 * @ORM\Entity
 */
class AuditDiff
{
    const ENTITY_NAME = 'OroEntityBundle:AuditDiff';

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
    protected $action;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $className;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $objectId;

    /**
     * @var AuditCommit
     * @ORM\ManyToOne(targetEntity="AuditCommit", inversedBy="diffs")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="commit_id", referencedColumnName="id")
     * })
     */
    protected $commit;

    /**
     * @var string
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
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param AuditCommit $commit
     * @return $this
     */
    public function setCommit(AuditCommit $commit)
    {
        $this->commit = $commit;

        return $this;
    }

    /**
     * @return AuditCommit
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param string $diff
     * @return $this
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }
}
