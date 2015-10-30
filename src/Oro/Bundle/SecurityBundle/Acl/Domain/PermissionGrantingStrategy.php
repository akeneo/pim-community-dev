<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\AuditLoggerInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * The ACL extensions based permission granting strategy to apply to the access control list.
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
     * @var ServiceLink
     */
    private $contextLink;

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
     * @param ServiceLink $contextLink The link to a service implementing PermissionGrantingStrategyContextInterface
     */
    public function setContext(ServiceLink $contextLink)
    {
        $this->contextLink = $contextLink;
    }

    /**
     * Gets context this strategy is working in
     *
     * @throws \RuntimeException
     * @return PermissionGrantingStrategyContextInterface
     */
    protected function getContext()
    {
        if ($this->contextLink === null) {
            throw new \RuntimeException('The context link is not set.');
        }

        return $this->contextLink->getService();
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
     * @throws NoAceFoundException
     * @return boolean|null true if granting access; false if denying access; null if ACE was not found.
     */
    protected function hasSufficientPermissions(
        AclInterface $acl,
        array $aces,
        array $masks,
        array $sids,
        $administrativeMode
    ) {
        $triggeredAce = null;
        $triggeredMask = 0;
        $result = false;

        foreach ($masks as $requiredMask) {
            foreach ($sids as $sid) {
                foreach ($aces as $ace) {
                    if ($sid->equals($ace->getSecurityIdentity())
                        && $this->isAceApplicable($requiredMask, $ace, $acl)
                    ) {
                        $isGranting = $ace->isGranting();

                        // give an additional chance for the appropriate ACL extension to decide
                        // whether an access to a domain object is granted or not
                        $decisionResult = $this->getContext()->getAclExtension()->decideIsGranting(
                            $requiredMask,
                            $this->getContext()->getObject(),
                            $this->getContext()->getSecurityToken()
                        );
                        if (!$decisionResult) {
                            $isGranting = !$isGranting;
                        }

                        if ($isGranting) {
                            // the access is granted if there is at least one granting ACE
                            $triggeredAce = $ace;
                            $triggeredMask = $requiredMask;
                            $result = true;
                            // break all loops when granting ACE was found
                            break 3;
                        } else {
                            // remember the first denying ACE
                            if (null === $triggeredAce) {
                                $triggeredAce = $ace;
                                $triggeredMask = $requiredMask;
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
            $this->auditLogger->logIfNeeded($result, $triggeredAce);
        }

        return $result;
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
     * @param AclInterface $acl
     * @throws \RuntimeException if the ACE strategy is not supported
     * @return bool
     */
    protected function isAceApplicable($requiredMask, EntryInterface $ace, AclInterface $acl)
    {
        $extension = $this->getContext()->getAclExtension();
        $aceMask = $ace->getMask();
        if ($acl->getObjectIdentity()->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            if ($acl->getObjectIdentity()->getIdentifier() !== $extension->getExtensionKey()) {
                return false;
            }
            $aceMask = $extension->adaptRootMask($aceMask, $this->getContext()->getObject());
        }
        if ($extension->getServiceBits($requiredMask) !== $extension->getServiceBits($aceMask)) {
            return false;
        }

        $requiredMask = $extension->removeServiceBits($requiredMask);
        $aceMask = $extension->removeServiceBits($aceMask);
        $strategy = $ace->getStrategy();
        switch ($strategy) {
            case self::ALL:
                return $requiredMask === ($aceMask & $requiredMask);
            case self::ANY:
                return 0 !== ($aceMask & $requiredMask);
            case self::EQUAL:
                return $requiredMask === $aceMask;
            default:
                throw new \RuntimeException(sprintf('The strategy "%s" is not supported.', $strategy));
        }
    }
}
