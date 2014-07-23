<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Category voter, allows to know if products of a category can be edited or consulted by a
 * user depending on his user groups
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryVoter implements VoterInterface
{
    /**
     * @var CategoryAccessManager
     */
    protected $accessManager;

    /**
     * @param CategoryAccessManager $accessManager
     */
    public function __construct(CategoryAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW_PRODUCTS, Attributes::EDIT_PRODUCTS]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof CategoryInterface;
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
                    $result        = VoterInterface::ACCESS_DENIED;
                    $grantedGroups = $this->extractGroups($attribute, $object);

                    foreach ($grantedGroups as $group) {
                        if ($token->getUser()->hasGroup($group)) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get user groups for specific attribute and object
     *
     * @param string            $attribute
     * @param CategoryInterface $object
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group[]
     */
    protected function extractGroups($attribute, $object)
    {
        if ($attribute === Attributes::EDIT_PRODUCTS) {
            $grantedGroups = $this->accessManager->getEditUserGroups($object);
        } else {
            $grantedGroups = $this->accessManager->getViewUserGroups($object);
        }

        return $grantedGroups;
    }
}
