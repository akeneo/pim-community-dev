<?php

namespace Oro\Bundle\ImapBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Provider\EmailBodyLoaderInterface;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Builder\EmailBodyBuilder;
use Oro\Bundle\ImapBundle\Connector\ImapConnectorFactory;
use Oro\Bundle\ImapBundle\Connector\ImapConfig;
use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;
use Oro\Bundle\ImapBundle\Entity\ImapEmail;

class ImapEmailBodyLoader implements EmailBodyLoaderInterface
{
    /**
     * @var ImapConnectorFactory
     */
    protected $connectorFactory;

    /**
     * Constructor
     *
     * @param ImapConnectorFactory $connectorFactory
     */
    public function __construct(ImapConnectorFactory $connectorFactory)
    {
        $this->connectorFactory = $connectorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(EmailOrigin $origin)
    {
        return $origin instanceof ImapEmailOrigin;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEmailBody(Email $email, EntityManager $em)
    {
        /** @var ImapEmailOrigin $origin */
        $origin = $email->getFolder()->getOrigin();

        $config = new ImapConfig(
            $origin->getHost(),
            $origin->getPort(),
            $origin->getSsl(),
            $origin->getUser(),
            $origin->getPassword()
        );
        $manager = new ImapEmailManager($this->connectorFactory->createImapConnector($config));
        $manager->selectFolder($email->getFolder()->getFullName());

        $repo = $em->getRepository('OroImapBundle:ImapEmail');
        $query = $repo->createQueryBuilder('e')
            ->select('partial e.{uid, uidValidity}')
            ->where('e.email = ?1')
            ->setParameter(1, $email)
            ->getQuery();
        /** @var ImapEmail $imapEmail */
        $imapEmail = $query->getSingleResult();

        $loadedEmail = $manager->findEmail($imapEmail->getUid());
        if ($loadedEmail === null) {
            throw new \RuntimeException(sprintf('Cannot find a body for "%s" email.', $email->getSubject()));
        }

        $builder = new EmailBodyBuilder();
        $builder->setEmailBody(
            $loadedEmail->getBody()->getContent(),
            $loadedEmail->getBody()->getBodyIsText()
        );
        foreach ($loadedEmail->getAttachments() as $attachment) {
            $builder->addEmailAttachment(
                $attachment->getFileName(),
                $attachment->getContent(),
                $attachment->getContentType(),
                $attachment->getContentTransferEncoding()
            );
        }

        return $builder->getEmailBody();
    }
}
