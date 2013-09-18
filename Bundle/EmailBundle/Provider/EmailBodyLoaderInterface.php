<?php

namespace Oro\Bundle\EmailBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailBody;

interface EmailBodyLoaderInterface
{
    /**
     * Checks if this loader can be used to load an email body from the given origin.
     *
     * @param EmailOrigin $origin
     * @return bool
     */
    public function supports(EmailOrigin $origin);

    /**
     * Loads email body for the given email
     *
     * @param Email $email
     * @param EntityManager $em
     * @return EmailBody
     */
    public function loadEmailBody(Email $email, EntityManager $em);
}
