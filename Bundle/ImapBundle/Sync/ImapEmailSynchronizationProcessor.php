<?php

namespace Oro\Bundle\ImapBundle\Sync;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Oro\Bundle\CronBundle\Command\Logger\LoggerInterface;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder;
use Oro\Bundle\ImapBundle\Entity\ImapEmail;
use Oro\Bundle\ImapBundle\Entity\ImapEmailFolder;
use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\Mail\Storage\Folder;
use Oro\Bundle\ImapBundle\Manager\DTO\Email;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

class ImapEmailSynchronizationProcessor
{
    const DB_BATCH_SIZE = 30;
    const EMAIL_ADDRESS_BATCH_SIZE = 10;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var ImapEmailManager
     */
    protected $manager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EmailEntityBuilder
     */
    protected $emailEntityBuilder;

    /**
     * Constructor
     *
     * @param LoggerInterface $log
     * @param ImapEmailManager $manager
     * @param EntityManager $em
     * @param EmailEntityBuilder $emailEntityBuilder
     * @param EmailAddressManager $emailAddressManager
     */
    public function __construct(
        LoggerInterface $log,
        ImapEmailManager $manager,
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager
    ) {
        $this->log = $log;
        $this->manager = $manager;
        $this->em = $em;
        $this->emailEntityBuilder = $emailEntityBuilder;
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * Performs a synchronization of emails for the given email origin.
     *
     * @param ImapEmailOrigin $origin
     */
    public function process(ImapEmailOrigin $origin)
    {
        $this->emailEntityBuilder->clear();

        $emailAddressBatches = $this->getKnownEmailAddressBatches();
        $folders = $this->getFolders($origin);

        foreach ($folders as $folder) {
            $this->emailEntityBuilder->setFolder($folder);

            $folderName = $folder->getFullName();
            $this->manager->selectFolder($folderName);

            $imapFolder = $this->getImapFolder($folder);
            if ($imapFolder->getUidValidity() !== $this->manager->getUidValidity()) {
                $imapFolder->setUidValidity($this->manager->getUidValidity());
                $this->em->persist($imapFolder);
                $this->em->flush();
            }

            $this->log->info(sprintf('Loading emails from "%s" folder ...', $folderName));

            foreach ($emailAddressBatches as $emailAddressBatch) {
                $sqb = $this->getSearchQueryBuilder($origin);
                $sqb->openParenthesis();
                if ($folder->getType() === EmailFolder::SENT) {
                    $this->addEmailAddressesToSearchQueryBuilder($sqb, 'to', $emailAddressBatch);
                    $sqb->orOperator();
                    $this->addEmailAddressesToSearchQueryBuilder($sqb, 'cc', $emailAddressBatch);
                    $sqb->orOperator();
                    $this->addEmailAddressesToSearchQueryBuilder($sqb, 'bcc', $emailAddressBatch);
                } else {
                    $this->addEmailAddressesToSearchQueryBuilder($sqb, 'from', $emailAddressBatch);
                }
                $sqb->closeParenthesis();

                $this->loadEmails($folder, $sqb->get());
            }
        }
    }

    /**
     * @param SearchQueryBuilder $sqb
     * @param string $addressType
     * @param string[] $addresses
     */
    protected function addEmailAddressesToSearchQueryBuilder(SearchQueryBuilder $sqb, $addressType, array $addresses)
    {
        for ($i = 0; $i < count($addresses); $i++) {
            if ($i > 0) {
                $sqb->orOperator();
            }
            $sqb->{$addressType}($addresses[$i]);
        }
    }

    /**
     * @param ImapEmailOrigin $origin
     * @return SearchQueryBuilder
     */
    protected function getSearchQueryBuilder(ImapEmailOrigin $origin)
    {
        $sqb = $this->manager->getSearchQueryBuilder();
        if ($origin->getSynchronizedAt()) {
            $sqb->sent($sqb->formatDate($origin->getSynchronizedAt()));
        } else {
            // this is the first synchronization of this folder; just load emails for last month
            $fromDate = new \DateTime('now');
            $fromDate = $fromDate->sub(new \DateInterval('P1M'));
            $sqb->sent($sqb->formatDate($fromDate));
        }

        return $sqb;
    }

    /**
     * Gets a list of email addresses which have an owner split into batches
     *
     * @return string[][]
     */
    protected function getKnownEmailAddressBatches()
    {
        $batches = array();
        $batchIndex = 0;
        $count = 0;
        foreach ($this->getKnownEmailAddresses() as $emailAddress) {
            if ($count >= self::EMAIL_ADDRESS_BATCH_SIZE) {
                $batchIndex++;
                $count = 0;
            }
            $batches[$batchIndex][$count] = $emailAddress;
            $count++;
        }

        return $batches;
    }

    /**
     * Gets a list of email addresses which have an owner
     *
     * @return string[]
     */
    protected function getKnownEmailAddresses()
    {
        $this->log->info('Loading known email addresses ...');

        $repo = $this->emailAddressManager->getEmailAddressRepository($this->em);
        $query = $repo->createQueryBuilder('a')
            ->select('a.email')
            ->where('a.hasOwner = ?1')
            ->setParameter(1, true)
            ->getQuery();
        $emailAddresses = $query->getArrayResult();

        $this->log->info(sprintf('Loaded %d email address(es).', count($emailAddresses)));

        return array_map(
            function ($el) {
                return $el['email'];
            },
            $emailAddresses
        );
    }

    /**
     * @param ImapEmailOrigin $origin
     * @return EmailFolder[]
     */
    protected function getFolders(ImapEmailOrigin $origin)
    {
        $this->log->info('Loading folders ...');

        $repo = $this->em->getRepository('OroEmailBundle:EmailFolder');
        $query = $repo->createQueryBuilder('f')
            ->where('f.origin = ?1')
            ->orderBy('f.name')
            ->setParameter(1, $origin)
            ->getQuery();
        $folders = $query->getResult();

        $this->log->info(sprintf('Loaded %d folder(s).', count($folders)));

        $this->ensureFoldersInitialized($folders, $origin);

        return $folders;
    }

    /**
     * @param EmailFolder[] $folders
     * @param ImapEmailOrigin $origin
     */
    protected function ensureFoldersInitialized(array &$folders, ImapEmailOrigin $origin)
    {
        if (!empty($folders)) {
            return;
        }

        $this->log->info('Retrieving folders from an email server ...');
        $srcFolders = $this->manager->getFolders(null, true);
        $this->log->info(sprintf('Retrieved %d folder(s).', count($srcFolders)));

        foreach ($srcFolders as $srcFolder) {
            $type = null;
            if ($srcFolder->hasFlag(Folder::FLAG_INBOX)) {
                $type = EmailFolder::INBOX;
            } elseif ($srcFolder->hasFlag(Folder::FLAG_SENT)) {
                $type = EmailFolder::SENT;
            }

            if ($type !== null) {
                $globalName = $srcFolder->getGlobalName();

                $this->log->info(sprintf('Persisting "%s" folder ...', $globalName));

                $folder = new EmailFolder();
                $folder
                    ->setFullName($globalName)
                    ->setName($srcFolder->getLocalName())
                    ->setType($type);

                $origin->addFolder($folder);

                $this->em->persist($origin);
                $this->em->persist($folder);

                $folders[] = $folder;

                $this->log->info(sprintf('The "%s" folder was persisted.', $globalName));
            }
        }

        $this->em->flush();
    }

    /**
     * Gets ImapEmailFolder entity related with the given EmailFolder entity
     *
     * @param EmailFolder $folder
     * @return ImapEmailFolder
     */
    protected function getImapFolder(EmailFolder $folder)
    {
        $this->log->info(sprintf('Load IMAP folder for "%s".', $folder->getFullName()));

        $repo = $this->em->getRepository('OroImapBundle:ImapEmailFolder');
        $query = $repo->createQueryBuilder('f')
            ->where('f.folder = ?1')
            ->setParameter(1, $folder)
            ->getQuery();

        try {
            $imapFolder = $query->getSingleResult();
        } catch (NoResultException $ex) {
            $this->log->info('IMAP folder does not exist. Create a new one.');
            $imapFolder = new ImapEmailFolder();
            $imapFolder->setFolder($folder);
        }

        return $imapFolder;
    }

    protected function loadEmails(EmailFolder $folder, SearchQuery $searchQuery)
    {
        $this->log->info(sprintf('Query: "%s".', $searchQuery->convertToSearchString()));
        $emails = $this->manager->getEmails($searchQuery);

        $count = 0;
        $batch = array();
        foreach ($emails as $email) {
            $count++;
            $batch[] = $email;
            if ($count === self::DB_BATCH_SIZE) {
                $this->saveEmails($batch, $folder);
                $count = 0;
                $batch = array();
            }
        }
        if ($count > 0) {
            $this->saveEmails($batch, $folder);
        }
    }

    /**
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
                $this->log->info(
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

                $this->log->info(sprintf('The "%s" email was persisted.', $src->getSubject()));
            } else {
                $this->log->info(
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
