<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence\Batch;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
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
    public function __construct(ObjectIdentityInterface $oid, int $state, ACL $acl = null)
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
    public function getOid(): OID
    {
        return $this->oid;
    }

    /**
     * Gets ACL of this item
     *
     * @return ACL
     */
    public function getAcl(): ACL
    {
        return $this->acl;
    }

    /**
     * Gets the state of this item
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * Sets the state of this item
     *
     * @param int $state
     */
    public function setState(int $state): void
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
    public function addAce(string $type, ?string $field, SID $sid, bool $granting, int $mask, ?string $strategy, bool $replace = false): void
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
    public function removeAce(string $type, ?string $field, SID $sid, bool $granting, int $mask, ?bool $strategy): void
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
    public function removeAces(string $type, ?string $field, SID $sid): void
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
