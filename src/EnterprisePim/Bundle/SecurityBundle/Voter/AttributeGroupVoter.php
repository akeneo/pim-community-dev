<?php

namespace EnterprisePim\Bundle\SecurityBundle\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

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

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array('VIEW_PRODUCT_DATA', 'EDIT'));
    }

    public function supportsClass($class)
    {
        return $class instanceof AttributeGroup;
    }

    function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($object)) {
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

