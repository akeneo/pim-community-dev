<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;

/**
 * Attribute group voter, allows to know if attributes of a group can be edited or consulted by a
 * user depending on his roles
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupVoter implements VoterInterface
{
    /** @var string */
    const VIEW_ATTRIBUTES = 'GROUP_VIEW_ATTRIBUTES';

    /** @var string */
    const EDIT_ATTRIBUTES = 'GROUP_EDIT_ATTRIBUTES';

    /**
     * @var AttributeGroupAccessManager
     */
    protected $accessManager;

    /**
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(AttributeGroupAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(self::VIEW_ATTRIBUTES, self::EDIT_ATTRIBUTES));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof AttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($object)) {
                $user = $token->getUser();

                if ($attribute === self::EDIT_ATTRIBUTES) {
                    $grantedRoles = $this->accessManager->getEditRoles($object);
                } else {
                    $grantedRoles = $this->accessManager->getViewRoles($object);
                }

                foreach ($grantedRoles as $role) {
                    if ($user->hasRole($role)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
