<?php

namespace Oro\Bundle\EmailBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

/**
 * This class responsible for binging EmailAddress to owner entities
 */
class EmailOwnerManager
{
    /**
     * A list of class names of all email owners
     *
     * @var string[]
     */
    protected $emailOwnerClasses = array();

    /**
     * @var EmailAddressManager
     */
    private $emailAddressManager;

    /**
     * Constructor.
     *
     * @param EmailOwnerProviderStorage $emailOwnerProviderStorage
     * @param EmailAddressManager $emailAddressManager
     */
    public function __construct(
        EmailOwnerProviderStorage $emailOwnerProviderStorage,
        EmailAddressManager $emailAddressManager
    ) {
        foreach ($emailOwnerProviderStorage->getProviders() as $provider) {
            $fieldName = sprintf('owner%d', count($this->emailOwnerClasses) + 1);
            $this->emailOwnerClasses[$fieldName] = $provider->getEmailOwnerClass();
        }
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * Handle onFlush event
     *
     * @param OnFlushEventArgs $event
     */
    public function handleOnFlush(OnFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $needChangeSetsComputing = false;

        $needChangeSetsComputing |= $this->handleInsertionsOrUpdates($uow->getScheduledEntityInsertions(), $em, $uow);
        $needChangeSetsComputing |= $this->handleInsertionsOrUpdates($uow->getScheduledEntityUpdates(), $em, $uow);
        $needChangeSetsComputing |= $this->handleDeletions($uow->getScheduledEntityDeletions(), $em);

        if ($needChangeSetsComputing) {
            $uow->computeChangeSets();
        }
    }

    /**
     * @param array $entities
     * @param EntityManager $em
     * @param UnitOfWork $uow
     * @return bool true if UnitOfWork change set need to be recomputed
     */
    protected function handleInsertionsOrUpdates(array $entities, EntityManager $em, UnitOfWork $uow)
    {
        $needChangeSetsComputing = false;
        foreach ($entities as $entity) {
            if ($entity instanceof EmailOwnerInterface) {
                $needChangeSetsComputing |= $this->processInsertionOrUpdateEntity(
                    $entity->getPrimaryEmailField(),
                    $entity,
                    $entity,
                    $em,
                    $uow
                );
            } elseif ($entity instanceof EmailInterface) {
                $needChangeSetsComputing |= $this->processInsertionOrUpdateEntity(
                    $entity->getEmailField(),
                    $entity,
                    $entity->getEmailOwner(),
                    $em,
                    $uow
                );
            }
        }

        return $needChangeSetsComputing;
    }

    /**
     * @param $emailField
     * @param mixed $entity
     * @param EmailOwnerInterface $owner
     * @param EntityManager $em
     * @param UnitOfWork $uow
     * @return bool true if UnitOfWork change set need to be recomputed
     */
    protected function processInsertionOrUpdateEntity(
        $emailField,
        $entity,
        EmailOwnerInterface $owner,
        EntityManager $em,
        UnitOfWork $uow
    ) {
        $needChangeSetsComputing = false;
        if (!empty($emailField)) {
            foreach ($uow->getEntityChangeSet($entity) as $field => $vals) {
                if ($field === $emailField) {
                    list($oldValue, $newValue) = $vals;
                    if ($newValue !== $oldValue) {
                        $needChangeSetsComputing |= $this->bindEmailAddress($em, $owner, $newValue, $oldValue);
                    }
                }
            }
        }

        return $needChangeSetsComputing;
    }

    /**
     * @param array $entities
     * @param EntityManager $em
     * @return bool true if UnitOfWork change set need to be recomputed
     */
    protected function handleDeletions(array $entities, EntityManager $em)
    {
        $needChangeSetsComputing = false;
        foreach ($entities as $entity) {
            if ($entity instanceof EmailOwnerInterface) {
                $needChangeSetsComputing |= $this->unbindEmailAddress($em, $entity);
            } elseif ($entity instanceof EmailInterface) {
                $needChangeSetsComputing |= $this->unbindEmailAddress($em, $entity->getEmailOwner(), $entity);
            }
        }

        return $needChangeSetsComputing;
    }

    /**
     * Bind EmailAddress entity to the given owner
     *
     * @param EntityManager $em
     * @param EmailOwnerInterface $owner
     * @param string $newEmail
     * @param string $oldEmail
     * @return bool true if UnitOfWork change set need to be recomputed
     */
    protected function bindEmailAddress(EntityManager $em, EmailOwnerInterface $owner, $newEmail, $oldEmail)
    {
        $result = false;
        $repository = $this->emailAddressManager->getEmailAddressRepository($em);
        if (!empty($newEmail)) {
            $emailAddress = $repository->findOneBy(array('email' => $newEmail));
            if ($emailAddress === null) {
                $em->persist($this->createEmailAddress($newEmail, $owner));
                $result = true;
            } elseif ($emailAddress->getOwner() != $owner) {
                $emailAddress->setOwner($owner);
                $result = true;
            }
        }
        if (!empty($oldEmail)) {
            $emailAddress = $repository->findOneBy(array('email' => $oldEmail));
            if ($emailAddress !== null) {
                $emailAddress->setOwner(null);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Unbind EmailAddress entity from the given owner
     *
     * @param EntityManager $em
     * @param EmailOwnerInterface $owner
     * @param EmailInterface $email
     * @return bool true if UnitOfWork change set need to be recomputed
     */
    protected function unbindEmailAddress(EntityManager $em, EmailOwnerInterface $owner, EmailInterface $email = null)
    {
        $result = false;
        $repository = $this->emailAddressManager->getEmailAddressRepository($em);
        foreach ($this->emailOwnerClasses as $fieldName => $emailOwnerClass) {
            $condition = array($fieldName => $owner);
            if ($email !== null) {
                $condition['email'] = $email->getEmail();
            }
            /** @var EmailAddress $emailAddress */
            foreach ($repository->findBy($condition) as $emailAddress) {
                $emailAddress->setOwner(null);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Create EmailAddress entity object
     *
     * @param string $email
     * @param EmailOwnerInterface $owner
     * @return EmailAddress
     */
    protected function createEmailAddress($email, EmailOwnerInterface $owner)
    {
        return $this->emailAddressManager->newEmailAddress()
            ->setEmail($email)
            ->setOwner($owner);
    }
}
