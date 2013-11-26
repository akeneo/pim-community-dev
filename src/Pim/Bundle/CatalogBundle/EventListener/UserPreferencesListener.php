<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Exception\LastAttributeOptionDeletedException;

/**
 * Aims to add/remove locales, channels and trees to user preference choices
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
    private $metadata = array();

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

        if ($entity instanceof Category && $entity->isRoot()) {
            $this->addOptionValue('defaulttree', $entity->getCode());
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

        if ($entity instanceof Category && $entity->isRoot()) {
            $this->removeOption('defaulttree', $entity->getCode());
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
     * Add a value as user attribute option for new locale, scope (=channel) or tree (=category)
     *
     * @param string $attributeCode
     * @param string $optionValue
     */
    protected function addOptionValue($attributeCode, $optionValue)
    {
        $userManager = $this->container->get('oro_user.manager');
        $attribute = $userManager->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if ($attribute) {
            $option = $userManager->createAttributeOption();
            $value  = $userManager->createAttributeOptionValue()->setValue($optionValue);
            $option->addOptionValue($value);
            $attribute->addOption($option);
            $this->computeChangeset($option);
            $this->computeChangeset($value);
        }
    }

    /**
     * Remove a value as user attribute option for removed locale, scope (=channel) or tree (=category)
     *
     * @param string $attributeCode
     * @param string $value
     *
     * @return null
     */
    protected function removeOption($attributeCode, $value)
    {
        $flexRepository = $this->container->get('oro_user.manager')->getFlexibleRepository();
        $attribute = $flexRepository->findAttributeByCode($attributeCode);

        if ($attribute) {
            $removedOption = $attribute->getOptions()->filter(
                function ($option) use ($value) {
                    return $option->getOptionValue()->getValue() == $value;
                }
            )->first();

            if (!$removedOption) {
                return;
            }

            $defaultOption = $attribute->getOptions()->filter(
                function ($option) use ($removedOption) {
                    return $option !== $removedOption;
                }
            )->first();

            if (!$defaultOption) {
                throw new LastAttributeOptionDeletedException(
                    sprintf('Tried to delete last %s attribute option', $attributeCode)
                );
            }

            $this->updateUserPreferences($attributeCode, $removedOption, $defaultOption);

            $attribute->removeOption($removedOption);
            $this->uow->scheduleForDelete($removedOption);
        }
    }

    /**
     * Sets user preferences to a new option if the previously selected option is deleted
     *
     * @param string          $attributeCode
     * @param AttributeOption $removedOption
     * @param AttributeOption $newOption
     *
     * @return null
     */
    protected function updateUserPreferences($attributeCode, AttributeOption $removedOption, AttributeOption $newOption)
    {
        $flexRepository = $this->container->get('oro_user.manager')->getFlexibleRepository();

        $usersQB = $flexRepository->findByWithAttributesQB(array($attributeCode));
        $flexRepository->applyFilterByAttribute(
            $usersQB,
            $attributeCode,
            array($removedOption->getId()), // $removedOption->getValue()->getId()
            'IN'
        );
        $users = $usersQB->getQuery()->getResult();
        foreach ($users as $user) {
            $value = $user->getValue($attributeCode);
            $value->setData($newOption);
            $this->computeChangeset($value);
        }
    }
}
