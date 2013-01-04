<?php
namespace Oro\Bundle\FlexibleEntityBundle\Listener;

use Oro\Bundle\FlexibleEntityBundle\Exception\HasRequiredValueException;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Events;

/**
 * Aims to add has value required behavior
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class HasRequiredValueListener implements EventSubscriber
{

    /**
     * Specifies the list of events to listen
     *
     * @return multitype:string
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     *
     * @throws HasValueRequiredException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->checkRequired($args);
    }

    /**
     * Before update
     *
     * @param LifecycleEventArgs $args
     *
     * @throws IsRequiredException
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->checkRequired($args);
    }

    /**
     * Check if all values required are set
     * @param LifecycleEventArgs $args
     *
     * @throws HasValueRequiredException
     */
    protected function checkRequired(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        $entityShortName = 'OroProductBundle:Product';

        if ($entity instanceof \Oro\Bundle\FlexibleEntityBundle\Model\Behavior\HasRequiredValueInterface) {
            // 1. Get Required Attributes
            $repo = $entityManager->getRepository($entityShortName);
            $attributes = $repo->getRequiredAttributes();

            // 2. Verify for each required attributes, value is set
            foreach ($attributes as $attribute) {
                if (!$entity->getValueData($attribute->getCode())) {
                    throw new HasRequiredValueException();
                }
            }
        }
    }
}
