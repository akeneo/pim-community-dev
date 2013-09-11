<?php

namespace Oro\Bundle\EmailBundle\Builder;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProvider;

class EmailEntityBatchProcessor implements EmailEntityBatchInterface
{
    /**
     * @var EmailAddressManager
     */
    protected $emailAddressManager;

    /**
     * @var EmailOwnerProvider
     */
    protected $emailOwnerProvider;

    /**
     * @var Email[]
     */
    protected $emails = array();

    /**
     * @var EmailAddress[]
     */
    protected $addresses = array();

    /**
     * @var EmailFolder[]
     */
    protected $folders = array();

    /**
     * @var EmailOrigin[]
     */
    protected $origins = array();

    /**
     * Constructor
     *
     * @param EmailAddressManager $emailAddressManager
     * @param EmailOwnerProvider $emailOwnerProvider
     */
    public function __construct(EmailAddressManager $emailAddressManager, EmailOwnerProvider $emailOwnerProvider)
    {
        $this->emailAddressManager = $emailAddressManager;
        $this->emailOwnerProvider = $emailOwnerProvider;
    }

    /**
     * Register Email object
     *
     * @param Email $obj
     */
    public function addEmail(Email $obj)
    {
        $this->emails[] = $obj;
    }

    /**
     * Register EmailAddress object
     *
     * @param EmailAddress $obj
     * @throws \LogicException
     */
    public function addAddress(EmailAddress $obj)
    {
        $key = strtolower($obj->getEmail());
        if (isset($this->addresses[$key])) {
            throw new \LogicException(sprintf('The email address "%s" already exists in the batch.', $obj->getEmail()));
        }
        $this->addresses[$key] = $obj;
    }

    /**
     * Get EmailAddress if it exists in the batch
     *
     * @param string $email The email address
     * @return EmailAddress|null
     */
    public function getAddress($email)
    {
        $key = strtolower($email);

        return isset($this->addresses[$key])
            ? $this->addresses[$key]
            : null;
    }

    /**
     * Register EmailFolder object
     *
     * @param EmailFolder $obj
     * @throws \LogicException
     */
    public function addFolder(EmailFolder $obj)
    {
        $key = strtolower(sprintf('%s_%s', $obj->getType(), $obj->getFullName()));
        if (isset($this->folders[$key])) {
            throw new \LogicException(
                sprintf('The folder "%s" (type: %s) already exists in the batch.', $obj->getFullName(), $obj->getType())
            );
        }
        $this->folders[$key] = $obj;
    }

    /**
     * Get EmailFolder if it exists in the batch
     *
     * @param string $type The folder type
     * @param string $fullName The full name of a folder
     * @return EmailFolder|null
     */
    public function getFolder($type, $fullName)
    {
        $key = strtolower(sprintf('%s_%s', $type, $fullName));

        return isset($this->folders[$key])
            ? $this->folders[$key]
            : null;
    }

    /**
     * Register EmailOrigin object
     *
     * @param EmailOrigin $origin
     * @throws \LogicException
     */
    public function addOrigin(EmailOrigin $origin)
    {
        $key = $origin->getId();
        if (isset($this->origins[$key])) {
            throw new \LogicException(sprintf('The origin %s already exists in the batch.', $origin));
        }
        $this->origins[$key] = $origin;
    }

    /**
     * Get EmailOrigin if it exists in the batch
     *
     * @param int $id The origin id
     * @return EmailOrigin|null
     */
    public function getOrigin($id)
    {
        return isset($this->origins[$id])
            ? $this->origins[$id]
            : null;
    }

    /**
     * Tell the given EntityManager to manage this batch
     *
     * @param EntityManager $em
     */
    public function persist(EntityManager $em)
    {
        $this->persistFolders($em);
        $this->persistAddresses($em);
        $this->persistEmails($em);
    }

    /**
     * Removes all objects from this batch processor
     */
    public function clear()
    {
        $this->emails = array();
        $this->folders = array();
        $this->origins = array();
        $this->addresses = array();
    }

    /**
     * Removes all email objects from this batch processor
     */
    public function removeEmails()
    {
        $this->emails = array();
    }

    /**
     * Tell the given EntityManager to manage Email objects and all its children in this batch
     *
     * @param EntityManager $em
     */
    protected function persistEmails(EntityManager $em)
    {
        foreach ($this->emails as $email) {
            $em->persist($email);
        }
    }

    /**
     * Tell the given EntityManager to manage EmailAddress objects in this batch
     *
     * @param EntityManager $em
     */
    protected function persistAddresses(EntityManager $em)
    {
        $repository = $this->emailAddressManager->getEmailAddressRepository($em);
        foreach ($this->addresses as $key => $obj) {
            /** @var EmailAddress $dbObj */
            $dbObj = $repository->findOneBy(array('email' => $obj->getEmail()));
            if ($dbObj === null) {
                $obj->setOwner($this->emailOwnerProvider->findEmailOwner($em, $obj->getEmail()));
                $em->persist($obj);
            } else {
                $this->updateAddressReferences($obj, $dbObj);
                $this->addresses[$key] = $dbObj;
            }
        }
    }

    /**
     * Tell the given EntityManager to manage EmailFolder objects in this batch
     *
     * @param EntityManager $em
     */
    protected function persistFolders(EntityManager $em)
    {
        $repository = $em->getRepository('OroEmailBundle:EmailFolder');
        foreach ($this->folders as $key => $obj) {
            if ($obj->getId() !== null) {
                continue;
            }
            /** @var EmailFolder $dbObj */
            $dbObj = $repository->findOneBy(array('fullName' => $obj->getFullName(), 'type' => $obj->getType()));
            if ($dbObj === null) {
                $em->persist($obj);
            } else {
                $this->updateFolderReferences($obj, $dbObj);
                $this->folders[$key] = $dbObj;
            }
        }
    }

    /**
     * Make sure that all objects in this batch have correct EmailAddress references
     *
     * @param EmailAddress $old
     * @param EmailAddress $new
     */
    protected function updateAddressReferences(EmailAddress $old, EmailAddress $new)
    {
        foreach ($this->emails as $email) {
            if ($email->getFromEmailAddress() === $old) {
                $email->setFromEmailAddress($new);
            }
            foreach ($email->getRecipients() as $recipient) {
                if ($recipient->getEmailAddress() === $old) {
                    $recipient->setEmailAddress($new);
                }
            }
        }
    }

    /**
     * Make sure that all objects in this batch have correct EmailFolder references
     *
     * @param EmailFolder $old
     * @param EmailFolder $new
     */
    protected function updateFolderReferences(EmailFolder $old, EmailFolder $new)
    {
        foreach ($this->emails as $obj) {
            if ($obj->getFolder() === $old) {
                $obj->setFolder($new);
            }
        }
    }
}
