<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Doctrine\ORM\Event\PreUpdateEventArgs;

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
            'prePersist',
            'preUpdate',
            'preRemove'
        );
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Channel) {
            $this->addOptionValue('catalogscope', $entity->getCode());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Channel) {
            $this->removeOption('catalogscope', $entity->getCode());
        }
    }

    /**
     * Before update
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Locale && $args->hasChangedField('activated')) {
            if ($args->getNewValue('activated')) {
                $this->addOptionValue('cataloglocale', $entity->getCode());
            } else {
                $this->removeOption('cataloglocale', $entity->getCode());
            }
        }
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
            $userManager->getStorageManager()->persist($attribute);
        }
    }
    
    /**
     * Remove a value as user attribute option for removed locale or removed scope (=channel)
     *
     * @param string $attributeCode
     * @param string $optionValue
     */
    protected function removeOption($attributeCode, $value)
    {
        $userManager = $this->container->get('oro_user.manager');
        $flexRepository = $userManager->getFlexibleRepository();
        $attribute = $flexRepository->findAttributeByCode($attributeCode);
        $storageManager = $userManager->getStorageManager();
        
        if ($attribute) {
            foreach ($attribute->getOptions() as $option) {
                if ($value == $option->getOptionValue()->getValue()) {
                    $removedOption = $option;
                } else {
                    $defaultOption = $option;
                }
                if (isset($removedOption) && isset($defaultOption)) {
                    break;
                }
            }
            if (!isset($defaultOption)) {
                throw new \LogicException(sprintf('Tried to delete last %s attribute option', $attributeCode));
            }
            
            $usersQB = $flexRepository->findByWithAttributesQB(array($attributeCode));
            $flexRepository->applyFilterByAttribute($usersQB, $attributeCode, array($removedOption->getOptionValue()->getId()), 'IN');
            $users = $usersQB->getQuery()->getResult();
            foreach ($users as $user) {
                $value = $user->getValue($attributeCode);
                $value->setData($defaultOption);
                $storageManager->persist($value);
            }
            
            $attribute->removeOption($removedOption);
            
            $storageManager->persist($attribute);
        }
    }
}
