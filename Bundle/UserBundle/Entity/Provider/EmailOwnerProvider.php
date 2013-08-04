<?php

namespace Oro\Bundle\UserBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass()
    {
        return 'Oro\Bundle\UserBundle\Entity\User';
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        /** @var User $user */
        $user = $em->getRepository('OroUserBundle:User')
            ->findOneBy(array('email' => $email));
        if ($user === null) {
            /** @var Email $emailEntity */
            $emailEntity = $em->getRepository('OroUserBundle:Email')
                ->findOneBy(array('email' => $email));
            if ($emailEntity !== null) {
                $user = $emailEntity->getUser();
            }
        }

        return $user;
    }
}
