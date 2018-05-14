<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Wrapper for Oro's ACL voter.
 * It can vote for any ACL of type "action", even if it doesn't match any existing annotation.
 *
 * Oro's SecurityBundle has its own ACL implementation on the top of Symfony's to handle Oro entities
 * and "@AclAncestor" annotations. It's convenient, but it cannot work in a firewall security context
 * since the object being voted is the Request (not an annotation or entity).
 *
 * We use this voter to build an ObjectIdentity instance and pass it to Oro's voter. It emulates the
 * fact that an action is being voted instead of a Request instance.
 *
 * Of course it is not an ideal solution. Better solutions include:
 * - Write an extension for the SecurityBundle, but it's hardly extensible without rewriting some parts.
 * - Use a different system for this case (it means more maintenance).
 * - Stop using ACLs and rewrite the whole security.
 *
 * @see Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector::select
 * @see Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter::vote
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ActionAclVoter extends Voter implements VoterInterface
{
    const OID_IDENTIFIER = 'action';

    /** @var VoterInterface */
    protected $baseAclVoter;

    /** @var string */
    protected $oidType;

    /**
     * @param VoterInterface $baseAclVoter
     * @param string         $oidType
     */
    public function __construct(VoterInterface $baseAclVoter, $oidType)
    {
        $this->baseAclVoter = $baseAclVoter;
        $this->oidType = $oidType;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $this->oidType === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (!$this->supports($attribute, $object)) {
                continue;
            }

            return $this->voteOnAttribute($attribute, $object, $token);
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $oid = new ObjectIdentity(static::OID_IDENTIFIER, $attribute);

        return $this->baseAclVoter->vote($token, $oid, ['EXECUTE']);
    }
}
