<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Model\AuditLoggerInterface;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;

/**
 * The owner based permission granting strategy to apply to the access control list.
 * The default Symfony permission granting strategy is supported as well.
 */
class PermissionGrantingStrategy implements PermissionGrantingStrategyInterface
{
    const OWNER = 'owner';
    const EQUAL = 'equal';
    const ALL = 'all';
    const ANY = 'any';

    /**
     * @var AuditLoggerInterface
     */
    protected $auditLogger;

    /**
     * @var OwnerProviderInterface
     */
    protected $ownerProvider;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Sets a provider to get owners
     *
     * @param OwnerProviderInterface $provider
     */
    public function setOwnerProvider(OwnerProviderInterface $provider)
    {
        $this->ownerProvider = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(AclInterface $acl, array $masks, array $sids, $administrativeMode = false)
    {
        $aces = $acl->getObjectAces();
        if ($aces) {
            return $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
        }
        $aces = $acl->getClassAces();
        if (!$aces) {
            return $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
        }
        if ($acl->isEntriesInheriting()) {
            $parentAcl = $acl->getParentAcl();
            if (null !== $parentAcl) {
                return $parentAcl->isGranted($masks, $sids, $administrativeMode);
            }
        }

        throw new NoAceFoundException();
    }

    /**
     * {@inheritDoc}
     */
    public function isFieldGranted(AclInterface $acl, $field, array $masks, array $sids, $administrativeMode = false)
    {
        $aces = $acl->getObjectFieldAces($field);
        if (!$aces) {
            return $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
        }
        $aces = $acl->getClassFieldAces($field);
        if (!$aces) {
            return $this->hasSufficientPermissions($acl, $aces, $masks, $sids, $administrativeMode);
        }
        if ($acl->isEntriesInheriting()) {
            $parentAcl = $acl->getParentAcl();
            if (null !== $parentAcl) {
                return $parentAcl->isFieldGranted($field, $masks, $sids, $administrativeMode);
            }
        }

        throw new NoAceFoundException();
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
     * permission/identity combinations are left. Finally, we will either throw
     * an NoAceFoundException, or deny access.
     *
     * @param AclInterface $acl
     * @param EntryInterface[] $aces An array of ACE to check against
     * @param array $masks An array of permission masks
     * @param SecurityIdentityInterface[] $sids An array of SecurityIdentityInterface implementations
     * @param boolean $administrativeMode True turns off audit logging
     *
     * @return boolean true, or false; either granting, or denying access respectively.
     * @throws NoAceFoundException
     */
    protected function hasSufficientPermissions(
        AclInterface $acl,
        array $aces,
        array $masks,
        array $sids,
        $administrativeMode
    ) {
        $firstRejectedAce = null;

        foreach ($masks as $requiredMask) {
            foreach ($sids as $sid) {
                foreach ($aces as $ace) {
                    if ($sid->equals($ace->getSecurityIdentity()) && $this->isAceApplicable($requiredMask, $ace)) {
                        if ($ace->isGranting()) {
                            if (!$administrativeMode && null !== $this->auditLogger) {
                                $this->auditLogger->logIfNeeded(true, $ace);
                            }

                            return true;
                        }

                        if (null === $firstRejectedAce) {
                            $firstRejectedAce = $ace;
                        }

                        break 2;
                    }
                }
            }
        }

        if (null !== $firstRejectedAce) {
            if (!$administrativeMode && null !== $this->auditLogger) {
                $this->auditLogger->logIfNeeded(false, $firstRejectedAce);
            }

            return false;
        }

        throw new NoAceFoundException();
    }

    /**
     * Determines whether the ACE is applicable to the given permission/security identity combination.
     *
     * Strategy OWNER:
     *     The ACE will be considered applicable when
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
     * @return boolean
     * @throws \RuntimeException if the ACE strategy is not supported
     */
    protected function isAceApplicable($requiredMask, EntryInterface $ace)
    {
        $strategy = $ace->getStrategy();
        switch ($strategy) {
            case self::OWNER:
                return $this->isOwnerAceApplicable($requiredMask, $ace);
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

    /**
     * @param integer $requiredMask
     * @param EntryInterface $ace
     * @return boolean
     */
    protected function isOwnerAceApplicable($requiredMask, EntryInterface $ace)
    {
        return false;
    }
}
