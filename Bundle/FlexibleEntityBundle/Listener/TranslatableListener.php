<?php
namespace Oro\Bundle\FlexibleEntityBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Helper\LocaleHelper;

/**
 * Aims to inject locale and default locale code to loaded entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TranslatableListener implements EventSubscriber
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad'
        );
    }

    /**
     * Set locale helper
     * @param LocaleHelper $localeHelper
     */
    public function setLocaleHelper(LocaleHelper $localeHelper = null)
    {
        $this->localeHelper = $localeHelper;
    }

    /**
     * Get locale helper
     * @return LocaleHelper
     */
    public function getLocaleHelper()
    {
        return $this->localeHelper;
    }

    /**
     * After load
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        // add locale and default locale codes on translatable containers
        if ($entity instanceof \Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableContainerInterface) {
            $entity->setDefaultLocaleCode($this->getLocaleHelper()->getDefaultLocaleCode());
            $entity->setLocaleCode($this->getLocaleHelper()->getCurrentLocaleCode());
        }
    }

}