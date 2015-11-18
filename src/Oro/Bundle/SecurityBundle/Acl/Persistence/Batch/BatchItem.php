<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence\Batch;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as OID;
use Symfony\Component\Security\Acl\Model\MutableAclInterface as ACL;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;

class BatchItem
{
    const STATE_NONE = 0;
    const STATE_CREATE = 1;
    const STATE_UPDATE = 2;
    const STATE_DELETE = 3;

    /**
     * @var OID
     */
    private $oid;

    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var int
     */
    private $state;

    /**
     * Array of ACEs. This is used only for new ACL (state = CREATE)
     *
     * @var ArrayCollection|Ace[]
     */
    private $aces = null;

    /**
     * Constructor
     *
     * @param OID $oid
     * @param int $state
     * @param ACL $acl
     */
    public function __construct(OID $oid, $state, ACL $acl = null)
    {
        $this->oid = $oid;
        $this->state = $state;
        $this->acl = $acl;
    }

    /**
     * Gets ObjectIdentity of this item
     *
     * @return OID
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Gets ACL of this item
     *
     * @return ACL
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Gets the state of this item
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the state of this item
     *
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Gets ACEs associated with this item
     *
     * @return Ace[]
     */
    public function getAces()
    {
        return $this->aces === null
            ? []
            : $this->aces;
    }

    /**
     * Associates an ACE with the given attributes with this item
     *
     * @param string      $type  The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param SID         $sid
     * @param bool        $granting
     * @param int         $mask
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @param bool $replace If true the mask and strategy of the existing ACE should be replaced with the given ones
     */
    public function addAce($type, $field, SID $sid, $granting, $mask, $strategy, $replace = false)
    {
        if ($this->aces === null) {
            $this->aces = new ArrayCollection();
        }
        $this->aces->add(new Ace($type, $field, $sid, $granting, $mask, $strategy, $replace));
    }

    /**
     * Deletes an ACE with the given attributes from the list of ACEs associated with this item
     *
     * @param string      $type  The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param SID       $sid
     * @param bool      $granting
     * @param int       $mask
     * @param bool|null $strategy
     */
    public function removeAce($type, $field, SID $sid, $granting, $mask, $strategy)
    {
        if ($this->aces !== null) {
            $toRemoveKey = null;
            foreach ($this->aces as $key => $val) {
                if ($sid->equals($val->getSecurityIdentity())
                    && $type === $val->getType()
                    && $field === $val->getField()
                    && $granting === $val->isGranting()
                    && $mask === $val->getStrategy()
                    && $strategy === $val->getStrategy()
                ) {
                    $toRemoveKey = $key;
                    break;
                }
            }
            if ($toRemoveKey !== null) {
                $this->aces->remove($toRemoveKey);
            }
        }
    }

    /**
     * Deletes all ACEs the given type and security identity from the list of ACEs associated with this item
     *
     * @param string      $type  The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param SID $sid
     */
    public function removeAces($type, $field, SID $sid)
    {
        if ($this->aces !== null) {
            $toRemoveKeys = [];
            foreach ($this->aces as $key => $val) {
                if ($sid->equals($val->getSecurityIdentity())
                    && $type === $val->getType()
                    && $field === $val->getField()
                ) {
                    $toRemoveKeys[] = $key;
                    break;
                }
            }
            if (!empty($toRemoveKeys)) {
                foreach ($toRemoveKeys as $key) {
                    $this->aces->remove($key);
                }
            }
        }
    }
}
