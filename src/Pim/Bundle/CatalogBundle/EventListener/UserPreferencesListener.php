<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;

/**
 * Aims to add / remove locales and channels
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesListener implements EventSubscriber
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var EntityManager $manager
     */
    protected $manager;

    /**
     * @var UnitOfWork $uow
     */
    protected $uow;

    /**
     * @var array
     */
    private $metadata=array();

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return ScopableListener
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
        );
    }

    /**
     * On flush
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->manager = $args->getEntityManager();
        $this->uow = $this->manager->getUnitOfWork();
        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            $this->prePersist($entity);
        }
        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            $this->preUpdate($entity);
        }
        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            $this->preRemove($entity);
        }
    }

    /**
     * Before insert
     *
     * @param object $entity
     */
    protected function prePersist($entity)
    {
        if ($entity instanceof Channel) {
            $this->addOptionValue('catalogscope', $entity->getCode());
        }
    }

    /**
     * Before remove
     *
     * @param object $entity
     */
    protected function preRemove($entity)
    {
        if ($entity instanceof Channel) {
            $this->removeOption('catalogscope', $entity->getCode());
        }
    }

    /**
     * Before update
     *
     * @param object $entity
     */
    protected function preUpdate($entity)
    {
        if ($entity instanceof Locale) {
            $changeset = $this->uow->getEntityChangeSet($entity);
            if (isset($changeset['activated'])) {
                if ($changeset['activated'][1]) {
                    $this->addOptionValue('cataloglocale', $entity->getCode());
                } else {
                    $this->removeOption('cataloglocale', $entity->getCode());
                }
            }
        }
    }

    /**
     * Get the metadata of an entity
     * @param object $entity
     *
     * @return array
     */
    protected function getMetadata($entity)
    {
        $className = get_class($entity);
        if (!isset($this->metadata[$className])) {
            $this->metadata[$className] = $this->manager->getClassMetadata($className);
        }

        return $this->metadata[$className];
    }

    /**
     * Compute changeset
     * @param object $entity
     */
    protected function computeChangeset($entity)
    {
        $this->uow->persist($entity);
        $this->uow->computeChangeSet($this->getMetadata($entity), $entity);
    }
    /**
     * Add a value as user attribute option for new locale or new scope (=channel)
     *
     * @param string $attributeCode
     * @param string $optionValue
     */
    protected function addOptionValue($attributeCode, $optionValue)
    {
        $userManager = $this->container->get('oro_user.manager');
        $attribute = $userManager->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if ($attribute) {
            $option    = $userManager->createAttributeOption();
            $value     = $userManager->createAttributeOptionValue()->setValue($optionValue);
            $option->addOptionValue($value);
            $attribute->addOption($option);
            $this->computeChangeset($option);
            $this->computeChangeset($value);
        }
    }

    /**
     * Remove a value as user attribute option for removed locale or removed scope (=channel)
     *
     * @param string $attributeCode
     * @param string $value
     */
    protected function removeOption($attributeCode, $value)
    {
        $userManager = $this->container->get('oro_user.manager');
        $flexRepository = $userManager->getFlexibleRepository();
        $attribute = $flexRepository->findAttributeByCode($attributeCode);

        if ($attribute) {
            foreach ($attribute->getOptions() as $option) {
                if ($value == $option->getOptionValue()->getValue()) {
                    $removedOption = $option;
                } elseif (!isset($defaultOption)) {
                    $defaultOption = $option;
                }
                if (isset($removedOption) && isset($defaultOption)) {
                    break;
                }
            }
            if (!isset($defaultOption)) {
                throw new \LogicException(sprintf('Tried to delete last %s attribute option', $attributeCode));
            }

            // TODO : quick fix to pass behat, waiting for refactoring of that listener
            if (isset($removedOption)) {
                $usersQB = $flexRepository->findByWithAttributesQB(array($attributeCode));
                $flexRepository->applyFilterByAttribute(
                    $usersQB,
                    $attributeCode,
                    array($removedOption->getId()), //$removedOption->getValue()->getId()
                    'IN'
                );
                $users = $usersQB->getQuery()->getResult();
                foreach ($users as $user) {
                    $value = $user->getValue($attributeCode);
                    $value->setData($defaultOption);
                    $this->computeChangeset($value);
                }

                $attribute->removeOption($removedOption);
                $this->uow->scheduleForDelete($removedOption);
            }
        }
    }
}
