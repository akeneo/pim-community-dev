<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Category voter, allows to know if products of a category can be edited or consulted by a
 * user depending on his roles
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryVoter implements VoterInterface
{
    /** @staticvar string */
    const VIEW_PRODUCTS = 'CATEGORY_VIEW_PRODUCTS';

    /** @staticvar string */
    const EDIT_PRODUCTS = 'CATEGORY_EDIT_PRODUCTS';

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
        return in_array($attribute, [self::VIEW_PRODUCTS, self::EDIT_PRODUCTS]);
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
                    $result       = VoterInterface::ACCESS_DENIED;
                    $grantedRoles = $this->extractRoles($attribute, $object);

                    foreach ($grantedRoles as $role) {
                        if ($token->getUser()->hasRole($role)) {
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
     * @param string            $attribute
     * @param CategoryInterface $object
     *
     * @return Role[]
     */
    protected function extractRoles($attribute, $object)
    {
        if ($attribute === self::EDIT_PRODUCTS) {
            $grantedRoles = $this->accessManager->getEditRoles($object);
        } else {
            $grantedRoles = $this->accessManager->getViewRoles($object);
        }

        return $grantedRoles;
    }
}
