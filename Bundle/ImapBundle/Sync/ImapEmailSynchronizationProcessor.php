<?php

namespace Oro\Bundle\ImapBundle\Sync;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Psr\Log\LoggerInterface;
use Oro\Bundle\EmailBundle\Sync\AbstractEmailSynchronizationProcessor;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder;
use Oro\Bundle\ImapBundle\Entity\ImapEmail;
use Oro\Bundle\ImapBundle\Entity\ImapEmailFolder;
use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\ImapBundle\Mail\Storage\Folder;
use Oro\Bundle\ImapBundle\Manager\DTO\Email;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

/**
 * @todo the implemented synchronization algorithm is just a demo and it will be fixed soon
 */
class ImapEmailSynchronizationProcessor extends AbstractEmailSynchronizationProcessor
{
    const EMAIL_ADDRESS_BATCH_SIZE = 10;

    /**
     * @var ImapEmailManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param LoggerInterface $log
     * @param EntityManager $em
     * @param EmailEntityBuilder $emailEntityBuilder
     * @param EmailAddressManager $emailAddressManager
     * @param ImapEmailManager $manager
     */
    public function __construct(
        LoggerInterface $log,
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager,
        ImapEmailManager $manager
    ) {
        parent::__construct($log, $em, $emailEntityBuilder, $emailAddressManager);
        $this->manager = $manager;
    }

    /**
     * Performs a synchronization of emails for the given email origin.
     *
     * @param EmailOrigin $origin
     */
    public function process(EmailOrigin $origin)
    {
        // make sure that the entity builder is empty
        $this->emailEntityBuilder->clear();

        // get a list of emails belong to any object, for example an user or a contacts
        $emailAddressBatches = $this->getKnownEmailAddressBatches($origin->getSynchronizedAt());

        // iterate through all folders and do a synchronization of emails for each one
        $folders = $this->getFolders($origin);
        foreach ($folders as $folder) {
            // register the current folder in the entity builder
            $this->emailEntityBuilder->setFolder($folder);

            // ask an email server to select the current folder
            $folderName = $folder->getFullName();
            $this->manager->selectFolder($folderName);

            // check that a state of the current folder is valid
            $imapFolder = $this->getImapFolder($folder);
            if ($imapFolder->getUidValidity() !== $this->manager->getUidValidity()) {
                $imapFolder->setUidValidity($this->manager->getUidValidity());
                $this->em->persist($imapFolder);
                $this->em->flush();
            }

            $this->log->notice(sprintf('Loading emails from "%s" folder ...', $folderName));
            foreach ($emailAddressBatches as $emailAddressBatch) {
                // build a search query
                $sqb = $this->manager->getSearchQueryBuilder();
                if ($origin->getSynchronizedAt()
                    && $folder->getSynchronizedAt()
                    && !$emailAddressBatch['needFullSync']
                ) {
                    $sqb->sent($folder->getSynchronizedAt());
                }

                $sqb->openParenthesis();

                $sqb->openParenthesis();
                $this->addEmailAddressesToSearchQueryBuilder($sqb, 'from', $emailAddressBatch['items']);
                $sqb->closeParenthesis();

                $sqb->openParenthesis();
                $this->addEmailAddressesToSearchQueryBuilder($sqb, 'to', $emailAddressBatch['items']);
                $sqb->orOperator();
                $this->addEmailAddressesToSearchQueryBuilder($sqb, 'cc', $emailAddressBatch['items']);
                // not all IMAP servers support search by BCC, for example imap-mail.outlook.com does not
                //$sqb->orOperator();
                //$this->addEmailAddressesToSearchQueryBuilder($sqb, 'bcc', $emailAddressBatch['items']);
                $sqb->closeParenthesis();

                $sqb->closeParenthesis();

                // load emails using this search query
                $this->loadEmails($folder, $sqb->get());
            }
        }
    }

    /**
     * Adds the given email addresses to the search query.
     * Addresses are delimited by OR operator.
     *
     * @param SearchQueryBuilder $sqb
     * @param string $addressType
     * @param EmailAddress[] $addresses
     */
    protected function addEmailAddressesToSearchQueryBuilder(SearchQueryBuilder $sqb, $addressType, array $addresses)
    {
        for ($i = 0; $i < count($addresses); $i++) {
            if ($i > 0) {
                $sqb->orOperator();
            }
            $sqb->{$addressType}($addresses[$i]->getEmail());
        }
    }

    /**
     * Gets a list of email addresses which have an owner and splits them into batches
     *
     * @param \DateTime|null $lastSyncTime
     * @return array
     *             key = index
     *             value = array
     *                 'needFullSync' => true/false
     *                 'items' => EmailAddress[]
     */
    protected function getKnownEmailAddressBatches($lastSyncTime)
    {
        $batches = array();
        $batchIndex = 0;
        $count = 0;
        foreach ($this->getKnownEmailAddresses() as $emailAddress) {
            $needFullSync = !$lastSyncTime || $emailAddress->getUpdatedAt() > $lastSyncTime;
            if ($count >= self::EMAIL_ADDRESS_BATCH_SIZE
                || (isset($batches[$batchIndex]) && $needFullSync !== $batches[$batchIndex]['needFullSync'])
            ) {
                $batchIndex++;
                $count = 0;
            }
            if ($count === 0) {
                $batches[$batchIndex] = array('needFullSync' => $needFullSync, 'items' => array());
            }
            $batches[$batchIndex]['items'][$count] = $emailAddress;
            $count++;
        }

        return $batches;
    }

    /**
     * Gets a list of folders to be synchronized
     *
     * @param EmailOrigin $origin
     * @return EmailFolder[]
     */
    protected function getFolders(EmailOrigin $origin)
    {
        $this->log->notice('Loading folders ...');

        $repo = $this->em->getRepository('OroEmailBundle:EmailFolder');
        $query = $repo->createQueryBuilder('f')
            ->where('f.origin = ?1')
            ->orderBy('f.name')
            ->setParameter(1, $origin)
            ->getQuery();
        $folders = $query->getResult();

        $this->log->notice(sprintf('Loaded %d folder(s).', count($folders)));

        $this->ensureFoldersInitialized($folders, $origin);

        return $folders;
    }

    /**
     * Check the given folders and if needed correct them
     *
     * @param EmailFolder[] $folders
     * @param EmailOrigin $origin
     */
    protected function ensureFoldersInitialized(array &$folders, EmailOrigin $origin)
    {
        if (!empty($folders) && count($folders) >= 2) {
            return;
        }

        $this->log->notice('Retrieving folders from an email server ...');
        $srcFolders = $this->manager->getFolders(null, true);
        $this->log->notice(sprintf('Retrieved %d folder(s).', count($srcFolders)));

        foreach ($srcFolders as $srcFolder) {
            $type = null;
            if ($srcFolder->hasFlag(Folder::FLAG_INBOX)) {
                $type = EmailFolder::INBOX;
            } elseif ($srcFolder->hasFlag(Folder::FLAG_SENT)) {
                $type = EmailFolder::SENT;
            }

            if ($type !== null) {
                $globalName = $srcFolder->getGlobalName();
                if ($this->isFolderExist($folders, $type, $globalName)) {
                    continue;
                }

                $this->log->notice(sprintf('Persisting "%s" folder ...', $globalName));

                $folder = new EmailFolder();
                $folder
                    ->setFullName($globalName)
                    ->setName($srcFolder->getLocalName())
                    ->setType($type);

                $origin->addFolder($folder);

                $this->em->persist($origin);
                $this->em->persist($folder);

                $folders[] = $folder;

                $this->log->notice(sprintf('The "%s" folder was persisted.', $globalName));
            }
        }

        $this->em->flush();
    }

    /**
     * Checks if the folder exists in the given list
     *
     * @param EmailFolder[] $folders
     * @param string $folderType
     * @param string $folderGlobalName
     * @return bool
     */
    protected function isFolderExist(array &$folders, $folderType, $folderGlobalName)
    {
        $exists = false;
        foreach ($folders as $folder) {
            if ($folder->getType() === $folderType && $folder->getFullName() === $folderGlobalName) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * Gets ImapEmailFolder entity connected to the given EmailFolder entity
     *
     * @param EmailFolder $folder
     * @return ImapEmailFolder
     */
    protected function getImapFolder(EmailFolder $folder)
    {
        $this->log->notice(sprintf('Load IMAP folder for "%s".', $folder->getFullName()));

        $repo = $this->em->getRepository('OroImapBundle:ImapEmailFolder');
        $query = $repo->createQueryBuilder('f')
            ->where('f.folder = ?1')
            ->setParameter(1, $folder)
            ->getQuery();

        try {
            $imapFolder = $query->getSingleResult();
        } catch (NoResultException $ex) {
            $this->log->notice('IMAP folder does not exist. Create a new one.');
            $imapFolder = new ImapEmailFolder();
            $imapFolder->setFolder($folder);
        }

        return $imapFolder;
    }

    /**
     * Loads emails from an email server and save them into the database
     *
     * @param EmailFolder $folder
     * @param SearchQuery $searchQuery
     */
    protected function loadEmails(EmailFolder $folder, SearchQuery $searchQuery)
    {
        $this->log->notice(sprintf('Query: "%s".', $searchQuery->convertToSearchString()));
        $folder->setSynchronizedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $emails = $this->manager->getEmails($searchQuery);

        $needFolderFlush = true;
        $count = 0;
        $batch = array();
        foreach ($emails as $email) {
            $count++;
            $batch[] = $email;
            if ($count === self::DB_BATCH_SIZE) {
                $this->saveEmails($batch, $folder);
                $needFolderFlush = false;
                $count = 0;
                $batch = array();
            }
        }
        if ($count > 0) {
            $this->saveEmails($batch, $folder);
            $needFolderFlush = false;
        }

        if ($needFolderFlush) {
            $this->em->flush();
        }
    }

    /**
     * Saves emails into the database
     *
     * @param Email[] $emails
     * @param EmailFolder $folder
     */
    protected function saveEmails(array $emails, EmailFolder $folder)
    {
        $this->emailEntityBuilder->removeEmails();

        $uids = array_map(
            function ($el) {
                /** @var Email $el */
                return $el->getId()->getUid();
            },
            $emails
        );

        $repo = $this->em->getRepository('OroImapBundle:ImapEmail');
        $query = $repo->createQueryBuilder('e')
            ->select('e.uid')
            ->innerJoin('e.email', 'se')
            ->innerJoin('se.folder', 'sf')
            ->where('sf.id = :folderId AND e.uid IN (:uids)')
            ->setParameter('folderId', $folder->getId())
            ->setParameter('uids', $uids)
            ->getQuery();
        $existingUids = array_map(
            function ($el) {
                return $el['uid'];
            },
            $query->getResult()
        );

        foreach ($emails as $src) {
            if (!in_array($src->getId()->getUid(), $existingUids)) {
                $this->log->notice(
                    sprintf('Persisting "%s" email (UID: %d) ...', $src->getSubject(), $src->getId()->getUid())
                );

                $email = $this->emailEntityBuilder->email(
                    $src->getSubject(),
                    $src->getFrom(),
                    $src->getToRecipients(),
                    $src->getSentAt(),
                    $src->getReceivedAt(),
                    $src->getInternalDate(),
                    $src->getImportance(),
                    $src->getCcRecipients(),
                    $src->getBccRecipients()
                );
                $email->setFolder($folder);
                $imapEmail = new ImapEmail();
                $imapEmail
                    ->setUid($src->getId()->getUid())
                    ->setEmail($email);
                $this->em->persist($imapEmail);

                $this->log->notice(sprintf('The "%s" email was persisted.', $src->getSubject()));
            } else {
                $this->log->notice(
                    sprintf(
                        'Skip "%s" (UID: %d) email, because it is already synchronised.',
                        $src->getSubject(),
                        $src->getId()->getUid()
                    )
                );
            }
        }

        $this->emailEntityBuilder->getBatch()->persist($this->em);
        $this->em->flush();
    }
}
