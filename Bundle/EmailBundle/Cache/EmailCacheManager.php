<?php

namespace Oro\Bundle\EmailBundle\Cache;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Provider\EmailBodyLoaderSelector;

class EmailCacheManager
{
    /**
     * @var EmailBodyLoaderSelector
     */
    private $selector;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EmailBodyLoaderSelector $selector
     * @param EntityManager $em
     */
    public function __construct(EmailBodyLoaderSelector $selector, EntityManager $em)
    {
        $this->selector = $selector;
        $this->em = $em;
    }

    /**
     * Check that email body is cached.
     * If do not, load it using appropriate email extension add it to a cache.
     *
     * @param Email $email
     */
    public function ensureEmailBodyCached(Email $email)
    {
        if ($email->getEmailBody() !== null) {
            // The email body is already cached
            return;
        }

        $emailBody = $this->selector
            ->select($email->getFolder()->getOrigin())
            ->loadEmailBody($email, $this->em);

        $emailBody->setHeader($email);
        $email->setEmailBody($emailBody);

        $this->em->persist($email);
        $this->em->flush();
    }
}
