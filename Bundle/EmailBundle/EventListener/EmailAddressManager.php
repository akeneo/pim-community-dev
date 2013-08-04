<?php

namespace Oro\Bundle\EmailBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

/**
 * This class responsible for configuration of EmailAddress entity and binging it to owner entities
 */
class EmailAddressManager
{
    /**
     * A list of class names of all email owners
     *
     * @var string[]
     */
    protected $emailOwnerClasses = array();

    /**
     * Add email owner provider
     *
     * @param EmailOwnerProviderInterface $provider
     */
    public function addProvider(EmailOwnerProviderInterface $provider)
    {
        $fieldName = sprintf('_owner%d', count($this->emailOwnerClasses) + 1);
        $this->emailOwnerClasses[$fieldName] = $provider->getEmailOwnerClass();
    }

    /**
     * Handle loadClassMetadata event
     *
     * @param LoadClassMetadataEventArgs $event
     */
    public function handleLoadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        if ($event->getClassMetadata()->getName() !== 'Oro\Bundle\EmailBundle\Entity\EmailAddress') {
            return;
        }

        $classMetadata = $event->getClassMetadata();
        foreach ($this->emailOwnerClasses as $fieldName => $emailOwnerClass) {
            $prefix = strtolower(substr($emailOwnerClass, 0, strpos($emailOwnerClass, '\\')));
            if ($prefix === 'oro' || $prefix === 'orocrm') {
                // do not use prefix if email's owner is a part of BAP and CRM
                $prefix = '';
            } else {
                $prefix .= '_';
            }
            $suffix = strtolower(substr($emailOwnerClass, strrpos($emailOwnerClass, '\\') + 1));

            $mapping = array(
                'targetEntity' => $emailOwnerClass,
                'fieldName' => $fieldName,
                'joinColumns' => array(
                    array(
                        'name' => sprintf('owner_%s%s_id', $prefix, $suffix),
                        'referencedColumnName' => 'id'
                    )
                ),
            );
            $classMetadata->mapManyToOne($mapping);
        }
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
        /** @var EmailAddress $emailAddress */
        $result = false;
        $repository = $em->getRepository('OroEmailBundle:EmailAddress');

        if (!empty($newEmail)) {
            $emailAddress = $this->findEmailAddress($newEmail, $repository);
            if ($emailAddress === null) {
                $em->persist($this->createEmailAddress($newEmail, $owner));
                $result = true;
            } elseif ($emailAddress->getOwner() != $owner) {
                $emailAddress->setOwner($owner);
                $result = true;
            }
        }
        if (!empty($oldEmail)) {
            $emailAddress = $this->findEmailAddress($oldEmail, $repository);
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
        /** @var EmailAddress $emailAddress */
        $result = false;
        $repository = $em->getRepository('OroEmailBundle:EmailAddress');
        foreach ($this->emailOwnerClasses as $fieldName => $emailOwnerClass) {
            $condition = array($fieldName => $owner);
            if ($email !== null) {
                $condition['email'] = $email->getEmail();
            }
            foreach ($repository->findBy($condition) as $emailAddress) {
                $emailAddress->setOwner(null);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Find EmailAddress entity by email address
     *
     * @param string $email
     * @param EntityRepository $repository
     * @return EmailAddress
     */
    protected function findEmailAddress($email, EntityRepository $repository)
    {
        return $repository->findOneBy(array('email' => $email));
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
        $emailAddress = new EmailAddress();
        $emailAddress
            ->setEmail($email)
            ->setOwner($owner);

        return $emailAddress;
    }
}
