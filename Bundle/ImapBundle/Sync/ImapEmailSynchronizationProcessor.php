<?php

namespace Oro\Bundle\ImapBundle\Sync;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Oro\Bundle\CronBundle\Command\Logger\LoggerInterface;
use Oro\Bundle\ImapBundle\Entity\ImapEmail;
use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\Entity\ImapEmailFolder;
use Oro\Bundle\ImapBundle\Mail\Storage\Folder;
use Oro\Bundle\ImapBundle\Manager\DTO\Email;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;

class ImapEmailSynchronizationProcessor
{
    const BATCH_SIZE = 10;

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
     */
    public function __construct(
        LoggerInterface $log,
        ImapEmailManager $manager,
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder
    ) {
        $this->log = $log;
        $this->manager = $manager;
        $this->em = $em;
        $this->emailEntityBuilder = $emailEntityBuilder;
    }

    /**
     * Performs a synchronization of emails for the given email origin.
     *
     * @param ImapEmailOrigin $origin
     */
    public function process(ImapEmailOrigin $origin)
    {
        $this->emailEntityBuilder->clear();

        $folders = $this->getFolders($origin);

        foreach ($folders as $folder) {
            $this->emailEntityBuilder->setFolder($folder->getFolder());

            $folderName = $folder->getFolder()->getFullName();
            $this->manager->selectFolder($folderName);
            $qb = $this->manager->getSearchQueryBuilder();

            if ($folder->getSynchronizedAt()) {
                $qb->sent($qb->formatDate($folder->getSynchronizedAt()));
            } else {
                // this is the first synchronization of this folder; just load emails for last month
                $fromDate = new \DateTime('now');
                $fromDate = $fromDate->sub(new \DateInterval('P1M'));
                $qb->sent($qb->formatDate($fromDate));
            }

            $this->log->info(sprintf('Loading emails from "%s" folder ...', $folderName));
            $emails = $this->manager->getEmails($qb->get(), 21);

            $inBatchCount = 0;
            $batch = array();
            foreach ($emails as $email) {
                $inBatchCount++;
                $batch[] = $email;
                if ($inBatchCount === self::BATCH_SIZE) {
                    $this->saveEmails($batch, $this->manager->getUidValidity(), $folder);
                    $inBatchCount = 0;
                    $batch = array();
                }
            }
            if ($inBatchCount > 0) {
                $this->saveEmails($batch, $this->manager->getUidValidity(), $folder);
            }
        }
    }

    /**
     * @param ImapEmailOrigin $origin
     * @return ImapEmailFolder[]
     */
    protected function getFolders(ImapEmailOrigin $origin)
    {
        $this->log->info('Loading folders ...');

        $repo = $this->em->getRepository('OroImapBundle:ImapEmailFolder');
        $query = $repo->createQueryBuilder('f')
            ->select('f, sf')
            ->innerJoin('f.folder', 'sf')
            ->where('sf.origin = ?1')
            ->orderBy('f.syncCode, f.synchronizedAt, sf.name')
            ->setParameter(1, $origin)
            ->getQuery();
        $folders = $query->getResult();

        $this->log->info(sprintf('Loaded %d folder(s).', count($folders)));

        $this->ensureFoldersInitialized($folders, $origin);

        return $folders;
    }

    /**
     * @param ImapEmailFolder[] $folders
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

                $imapFolder = new ImapEmailFolder();
                $imapFolder->setFolder($folder);

                $origin->addFolder($folder);

                $this->em->persist($origin);
                $this->em->persist($imapFolder);

                $folders[] = $imapFolder;

                $this->log->info(sprintf('The "%s" folder was persisted.', $globalName));
            }
        }
    }

    /**
     * @param Email[] $emails
     * @param int $uidValidity
     * @param \Oro\Bundle\ImapBundle\Entity\ImapEmailFolder $folder
     */
    protected function saveEmails(array $emails, $uidValidity, ImapEmailFolder $folder)
    {
        $this->emailEntityBuilder->removeEmails();

        $uids = array();
        foreach ($emails as $src) {
            $uids[] = $src->getId()->getUid();
        }

        $repo = $this->em->getRepository('OroImapBundle:ImapEmail');
        $query = $repo->createQueryBuilder('e')
            ->select('e.uid')
            ->innerJoin('e.email', 'se')
            ->innerJoin('se.folder', 'sf')
            ->where('sf.id = :folderId AND e.uidValidity = :uidValidity AND e.uid IN (:uids)')
            ->setParameter('folderId', $folder->getFolder()->getId())
            ->setParameter('uidValidity', $uidValidity)
            ->setParameter('uids', $uids)
            ->getQuery();
        $existingUids = $query->getResult();

        foreach ($emails as $src) {
            if (!in_array($src->getId()->getUid(), $existingUids)) {
                $this->log->info(sprintf('Persisting "%s" email ...', $src->getSubject()));

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
                $imapEmail = new ImapEmail();
                $imapEmail
                    ->setUid($src->getId()->getUid())
                    ->setUidValidity($src->getId()->getUidValidity())
                    ->setEmail($email);
                $this->em->persist($imapEmail);

                $this->log->info(sprintf('The "%s" email was persisted.', $src->getSubject()));
            }
        }

        $this->emailEntityBuilder->getBatch()->persist($this->em);
    }
}
