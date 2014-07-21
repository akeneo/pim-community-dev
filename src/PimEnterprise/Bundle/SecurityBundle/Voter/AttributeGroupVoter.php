<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Attribute group voter, allows to know if attributes of a group can be edited or consulted by a
 * user depending on his user groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupVoter implements VoterInterface
{
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
        return in_array($attribute, array(Attributes::VIEW_ATTRIBUTES, Attributes::EDIT_ATTRIBUTES));
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
        $result = VoterInterface::ACCESS_ABSTAIN;

        if ($this->supportsClass($object)) {
            foreach ($attributes as $attribute) {
                if ($this->supportsAttribute($attribute)) {
                    $result       = VoterInterface::ACCESS_DENIED;
                    $grantedUserGroups = $this->extractUserGroups($attribute, $object);

                    foreach ($grantedUserGroups as $userGroup) {
                        if ($token->getUser()->hasGroup($userGroup)) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get roles for specific attribute and object
     *
     * @param string         $attribute
     * @param AttributeGroup $object
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group[]
     */
    protected function extractUserGroups($attribute, $object)
    {
        if ($attribute === Attributes::EDIT_ATTRIBUTES) {
            $grantedGroups = $this->accessManager->getEditUserGroups($object);
        } else {
            $grantedGroups = $this->accessManager->getViewUserGroups($object);
        }

        return $grantedGroups;
    }
}
