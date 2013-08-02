<?php

namespace Oro\Bundle\UserBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner($emailAddress)
    {
        /** @var User $user */
        $user = $this->em->getRepository('Oro\Bundle\UserBundle\Entity\User')
            ->findOneBy(array('email' => $emailAddress));
        if ($user === null) {
            /** @var Email $email */
            $email = $this->em->getRepository('Oro\Bundle\UserBundle\Entity\Email')
                ->findOneBy(array('email' => $emailAddress));
            if ($email !== null) {
                $user = $email->getUser();
            }
        }

        return $user;
    }
}
