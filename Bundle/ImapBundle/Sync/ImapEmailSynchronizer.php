<?php

namespace Oro\Bundle\ImapBundle\Sync;

use Oro\Bundle\EmailBundle\Sync\AbstractEmailSynchronizer;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\ImapBundle\Connector\ImapConfig;
use Oro\Bundle\ImapBundle\Connector\ImapConnectorFactory;
use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;
use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class ImapEmailSynchronizer extends AbstractEmailSynchronizer
{
    /**
     * @var ImapConnectorFactory
     */
    protected $connectorFactory;

    /** @var Mcrypt */
    protected $encryptor;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param EmailEntityBuilder $emailEntityBuilder
     * @param EmailAddressManager $emailAddressManager
     * @param ImapConnectorFactory $connectorFactory
     * @param Mcrypt $encryptor
     */
    public function __construct(
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager,
        ImapConnectorFactory $connectorFactory,
        Mcrypt $encryptor
    ) {
        parent::__construct($em, $emailEntityBuilder, $emailAddressManager);
        $this->connectorFactory = $connectorFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEmailOriginClass()
    {
        return 'OroImapBundle:ImapEmailOrigin';
    }

    /**
     * Creates a processor is used to synchronize emails
     *
     * @param ImapEmailOrigin $origin
     * @return ImapEmailSynchronizationProcessor
     */
    protected function createSynchronizationProcessor($origin)
    {
        $config = new ImapConfig(
            $origin->getHost(),
            $origin->getPort(),
            $origin->getSsl(),
            $origin->getUser(),
            $this->encryptor->decryptData($origin->getPassword())
        );

        return new ImapEmailSynchronizationProcessor(
            $this->log,
            $this->em,
            $this->emailEntityBuilder,
            $this->emailAddressManager,
            new ImapEmailManager($this->connectorFactory->createImapConnector($config))
        );
    }
}
