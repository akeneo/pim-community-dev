<?php

namespace Oro\Bundle\EmailBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage;

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

        $this->handleInsertionsOrUpdates($uow->getScheduledEntityInsertions(), $em, $uow);
        $this->handleInsertionsOrUpdates($uow->getScheduledEntityUpdates(), $em, $uow);
        $this->handleDeletions($uow->getScheduledEntityDeletions(), $em);
    }

    /**
     * @param array $entities
     * @param EntityManager $em
     * @param UnitOfWork $uow
     */
    protected function handleInsertionsOrUpdates(array $entities, EntityManager $em, UnitOfWork $uow)
    {
        foreach ($entities as $entity) {
            if ($entity instanceof EmailOwnerInterface) {
                $this->processInsertionOrUpdateEntity(
                    $entity->getPrimaryEmailField(),
                    $entity,
                    $entity,
                    $em,
                    $uow
                );
            } elseif ($entity instanceof EmailInterface) {
                $this->processInsertionOrUpdateEntity(
                    $entity->getEmailField(),
                    $entity,
                    $entity->getEmailOwner(),
                    $em,
                    $uow
                );
            }
        }
    }

    /**
     * @param $emailField
     * @param mixed $entity
     * @param EmailOwnerInterface $owner
     * @param EntityManager $em
     * @param UnitOfWork $uow
     */
    protected function processInsertionOrUpdateEntity(
        $emailField,
        $entity,
        EmailOwnerInterface $owner,
        EntityManager $em,
        UnitOfWork $uow
    ) {
        if (!empty($emailField)) {
            foreach ($uow->getEntityChangeSet($entity) as $field => $vals) {
                if ($field === $emailField) {
                    list($oldValue, $newValue) = $vals;
                    if ($newValue !== $oldValue) {
                        $this->bindEmailAddress($em, $owner, $newValue, $oldValue);
                    }
                }
            }
        }
    }

    /**
     * @param array $entities
     * @param EntityManager $em
     * @return bool true if UnitOfWork change set need to be recomputed
     */
    protected function handleDeletions(array $entities, EntityManager $em)
    {
        foreach ($entities as $entity) {
            if ($entity instanceof EmailOwnerInterface) {
                $this->unbindEmailAddress($em, $entity);
            } elseif ($entity instanceof EmailInterface) {
                $this->unbindEmailAddress($em, $entity->getEmailOwner(), $entity);
            }
        }
    }

    /**
     * Bind EmailAddress entity to the given owner
     *
     * @param EntityManager $em
     * @param EmailOwnerInterface $owner
     * @param string $newEmail
     * @param string $oldEmail
     */
    protected function bindEmailAddress(EntityManager $em, EmailOwnerInterface $owner, $newEmail, $oldEmail)
    {
        $repository = $this->emailAddressManager->getEmailAddressRepository($em);
        if (!empty($newEmail)) {
            $emailAddress = $repository->findOneBy(array('email' => $newEmail));
            if ($emailAddress === null) {
                $emailAddress = $this->createEmailAddress($newEmail, $owner);
                $em->persist($emailAddress);
                $this->computeEntityChangeSet($em, $emailAddress);
            } elseif ($emailAddress->getOwner() != $owner) {
                $emailAddress->setOwner($owner);
                $this->computeEntityChangeSet($em, $emailAddress);
            }
        }
        if (!empty($oldEmail)) {
            $emailAddress = $repository->findOneBy(array('email' => $oldEmail));
            if ($emailAddress !== null) {
                $emailAddress->setOwner(null);
                $this->computeEntityChangeSet($em, $emailAddress);
            }
        }
    }

    /**
     * Unbind EmailAddress entity from the given owner
     *
     * @param EntityManager $em
     * @param EmailOwnerInterface $owner
     * @param EmailInterface $email
     */
    protected function unbindEmailAddress(EntityManager $em, EmailOwnerInterface $owner, EmailInterface $email = null)
    {
        $repository = $this->emailAddressManager->getEmailAddressRepository($em);
        foreach ($this->emailOwnerClasses as $fieldName => $emailOwnerClass) {
            $condition = array($fieldName => $owner);
            if ($email !== null) {
                $condition['email'] = $email->getEmail();
            }
            /** @var EmailAddress $emailAddress */
            foreach ($repository->findBy($condition) as $emailAddress) {
                $emailAddress->setOwner(null);
                $this->computeEntityChangeSet($em, $emailAddress);
            }
        }
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

    /**
     * @param EntityManager $entityManager
     * @param mixed $entity
     */
    protected function computeEntityChangeSet(EntityManager $entityManager, $entity)
    {
        $entityClass = ClassUtils::getClass($entity);
        $classMetadata = $entityManager->getClassMetadata($entityClass);
        $unitOfWork = $entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSet($classMetadata, $entity);
    }
}
