<?php

namespace Pim\Bundle\ProductBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Locale;

/**
 * Aims to add / remove locales and channels
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
            'prePersist'
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

        if ($entity instanceof Locale) {
            $this->addOptionValue('cataloglocale', $entity->getCode());

        } elseif ($entity instanceof Channel) {
            $this->addOptionValue('catalogscope', $entity->getCode());
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
}
