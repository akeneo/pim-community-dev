<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence\Batch;

use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class Ace
{
    /**
     * @var SecurityIdentityInterface
     */
    private $sid;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $field;

    /**
     * @var int
     */
    private $mask;

    /**
     * @var bool
     */
    private $granting;

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var bool
     */
    private $replace;

    /**
     * Constructor
     *
     * @param string                    $type
     * @param string|null               $field
     * @param SecurityIdentityInterface $sid
     * @param bool                      $granting
     * @param int                       $mask
     * @param string|null               $strategy
     * @param bool                      $replace
     */
    public function __construct($type, $field, SecurityIdentityInterface $sid, $granting, $mask, $strategy, $replace)
    {
        $this->type = $type;
        $this->field = $field;
        $this->sid = $sid;
        $this->granting = $granting;
        $this->mask = $mask;
        $this->strategy = $strategy;
        $this->replace = $replace;
    }

    /**
     * Gets the security identity associated with this ACE
     *
     * @return SecurityIdentityInterface
     */
    public function getSecurityIdentity()
    {
        return $this->sid;
    }

    /**
     * Gets this ACE type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the name of a field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Gets the permission mask of this ACE
     *
     * @return int
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * Indicates whether this ACE is granting, or denying
     *
     * @return bool
     */
    public function isGranting()
    {
        return $this->granting;
    }

    /**
     * Gets the strategy for comparing masks
     *
     * @return string|null
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Indicates whether this ACE should replace existing ACE or not
     *
     * @return bool
     */
    public function isReplace()
    {
        return $this->replace;
    }
}
