<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Model\AuditLoggerInterface;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;

/**
 * The owner based permission granting strategy to apply to the access control list.
 * The default Symfony permission granting strategy is supported as well.
 */
class PermissionGrantingStrategy implements PermissionGrantingStrategyInterface
{
    const ALL = 'all';
    const ANY = 'any';
    const EQUAL = 'equal';

    /**
     * @var AuditLoggerInterface
     */
    protected $auditLogger;

    /**
     * @var PermissionGrantingStrategyContext
     */
    protected $context;

    /**
     * @var OwnershipDecisionMaker
     */
    protected $decisionMaker;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * Constructor
     */
    public function __construct(
        OwnershipDecisionMaker $decisionMaker,
        OwnershipMetadataProvider $metadataProvider
    ) {
        $this->decisionMaker = $decisionMaker;
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * Sets the audit logger
     *
     * @param AuditLoggerInterface $auditLogger
     */
    public function setAuditLogger(AuditLoggerInterface $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Sets the accessor to the context data of this strategy
     *
     * @param PermissionGrantingStrategyContext $context
     */
    public function setContext(PermissionGrantingStrategyContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(AclInterface $acl, array $masks, array $sids, $administrativeMode = false)
    {
        $result = null;

        // check object ACEs
        $aces = $acl->getObjectAces();
        if (!empty($aces)) {
            $result = $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
        }
        // check class ACEs if object ACEs were not found
        if ($result === null) {
            $aces = $acl->getClassAces();
            if (!empty($aces)) {
                $result = $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
            }
        }
        // check parent ACEs if object and class ACEs were not found
        if ($result === null && $acl->isEntriesInheriting()) {
            $parentAcl = $acl->getParentAcl();
            if ($parentAcl !== null) {
                $result = $parentAcl->isGranted($masks, $sids, $administrativeMode);
            }
        }
        // throw NoAceFoundException if no any ACEs were found
        if ($result === null) {
            throw new NoAceFoundException();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function isFieldGranted(AclInterface $acl, $field, array $masks, array $sids, $administrativeMode = false)
    {
        $result = null;

        // check object ACEs
        $aces = $acl->getObjectFieldAces($field);
        if (!empty($aces)) {
            $result = $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
        }
        // check class ACEs if object ACEs were not found
        if ($result === null) {
            $aces = $acl->getClassFieldAces($field);
            if (!empty($aces)) {
                $result = $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
            }
        }
        // check parent ACEs if object and class ACEs were not found
        if ($result === null && $acl->isEntriesInheriting()) {
            $parentAcl = $acl->getParentAcl();
            if ($parentAcl !== null) {
                $result = $parentAcl->isFieldGranted($field, $masks, $sids, $administrativeMode);
            }
        }
        // throw NoAceFoundException if no any ACEs were found
        if ($result === null) {
            throw new NoAceFoundException();
        }

        return $result;
    }

    /**
     * Makes an authorization decision.
     *
     * The order of ACEs, and SIDs is significant; the order of permission masks
     * not so much. It is important to note that the more specific security
     * identities should be at the beginning of the SIDs array in order for this
     * strategy to produce intuitive authorization decisions.
     *
     * First, we will iterate over permissions, then over security identities.
     * For each combination of permission, and identity we will test the
     * available ACEs until we find one which is applicable.
     *
     * The first applicable ACE will make the ultimate decision for the
     * permission/identity combination. If it is granting, this method will return
     * true, if it is denying, the method will continue to check the next
     * permission/identity combination.
     *
     * This process is repeated until either a granting ACE is found, or no
     * permission/identity combinations are left.
     *
     * @param AclInterface $acl
     * @param EntryInterface[] $aces An array of ACE to check against
     * @param array $masks An array of permission masks
     * @param SecurityIdentityInterface[] $sids An array of SecurityIdentityInterface implementations
     * @param boolean $administrativeMode True turns off audit logging
     *
     * @return boolean|null true if granting access; false if denying access; null if ACE was not found.
     * @throws NoAceFoundException
     */
    protected function hasSufficientPermissions(
        AclInterface $acl,
        array $aces,
        array $masks,
        array $sids,
        $administrativeMode
    ) {
        $triggeredAce = null;
        $isGrantingAce = false;

        foreach ($masks as $requiredMask) {
            foreach ($sids as $sid) {
                foreach ($aces as $ace) {
                    if ($sid->equals($ace->getSecurityIdentity())
                        && $this->isAceApplicable($requiredMask, $ace, $acl)
                    ) {
                        $isGranting = $ace->isGranting();
                        // check whether it is a domain object
                        $object = $this->context->getObject();
                        if ($object !== null && is_object($object) && !($object instanceof ObjectIdentityInterface)) {
                            $oid = $acl->getObjectIdentity();
                            $metadata = $this->metadataProvider->getMetadata($oid->getType());
                            if ($metadata->hasOwner()) {
                                $isApplicableByOwner = $this->isApplicableByOwner(
                                    $requiredMask,
                                    $ace,
                                    $acl,
                                    $object,
                                    $metadata
                                );
                                if (!$isApplicableByOwner) {
                                    $isGranting = !$isGranting;
                                }
                            }
                        }
                        if ($isGranting) {
                            // the access is granted if there is at least one granting ACE
                            $triggeredAce = $ace;
                            $isGrantingAce = true;
                            // break all loops when granting ACE was found
                            break 3;
                        } else {
                            // remember the first denying ACE
                            if (null === $triggeredAce) {
                                $triggeredAce = $ace;
                            }
                            // go to the next mask
                            break 2;
                        }
                    }
                }
            }
        }

        if ($triggeredAce === null) {
            // ACE was not found
            return null;
        }

        if (!$administrativeMode && null !== $this->auditLogger) {
            $this->auditLogger->logIfNeeded($isGrantingAce, $triggeredAce);
        }

        return $isGrantingAce;
    }

    /**
     * Determines whether the ACE is applicable to the given permission/security identity combination.
     *
     * Strategy ALL:
     *     The ACE will be considered applicable when all the turned-on bits in the
     *     required mask are also turned-on in the ACE mask.
     *
     * Strategy ANY:
     *     The ACE will be considered applicable when any of the turned-on bits in
     *     the required mask is also turned-on the in the ACE mask.
     *
     * Strategy EQUAL:
     *     The ACE will be considered applicable when the bitmasks are equal.
     *
     * @param integer $requiredMask
     * @param EntryInterface $ace
     * @return bool
     * @throws \RuntimeException if the ACE strategy is not supported
     */
    protected function isAceApplicable($requiredMask, EntryInterface $ace)
    {
        $strategy = $ace->getStrategy();
        switch ($strategy) {
            case self::ALL:
                return $requiredMask === ($ace->getMask() & $requiredMask);
            case self::ANY:
                return 0 !== ($ace->getMask() & $requiredMask);
            case self::EQUAL:
                return $requiredMask === $ace->getMask();
            default:
                throw new \RuntimeException(sprintf('The strategy "%s" is not supported.', $strategy));
        }
    }

    protected function isApplicableByOwner(
        $requiredMask,
        EntryInterface $ace,
        AclInterface $acl,
        $object,
        OwnershipMetadata $metadata
    ) {
        $user = $this->context->getSecurityToken()->getUser();
        if ($metadata->isOrganizationOwned()) {
            return $this->decisionMaker->isBelongToOrganization($user, $object);
        } elseif ($metadata->isBusinessUnitOwned()) {
            return $this->decisionMaker->isBelongToBusinessUnit($user, $object);
        } elseif ($metadata->isUserOwned()) {
            return $this->decisionMaker->isBelongToUser($user, $object);
        }

        throw new \RuntimeException(sprintf('Unhandled ownership for %s.', get_class($object)));
    }
}
