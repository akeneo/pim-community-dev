<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Enterprise Security Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupVoter implements VoterInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     * @param ObjectManager $objectManager the storage manager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array('VIEW_PRODUCT_DATA', 'EDIT_PRODUCT_DATA'));
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
    function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($object)) {

                // TODO : delegate this to a dedicated service / repository ?
                $role = $this->objectManager->getRepository('OroUserBundle:Role')->findOneByRole('ROLE_ADMINISTRATOR');
                $user = $token->getUser();
                if ($user->hasRole($role)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }
        
        return VoterInterface::ACCESS_DENIED;
    }
}
