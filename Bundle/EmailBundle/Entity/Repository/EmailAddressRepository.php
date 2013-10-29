<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;

class EmailAddressRepository extends EntityRepository
{
    /**
     * Finds a single email address entity by a value of its 'email' field.
     *
     * @param string $emailAddress
     * @return EmailAddress|null
     */
    public function findOneByEmail($emailAddress)
    {
        return $this->findOneBy(array('email' => $emailAddress));
    }
}
