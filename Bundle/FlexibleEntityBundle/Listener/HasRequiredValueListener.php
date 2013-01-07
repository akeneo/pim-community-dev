<?php
namespace Oro\Bundle\FlexibleEntityBundle\Listener;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\FlexibleEntityInterface;
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
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return HasRequiredValueListener
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

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

        // check entity is flexible
        if ($entity instanceof FlexibleEntityInterface) {

            // get flexible config
            $entityClass = get_class($entity);
            $flexibleConfig = $this->container->getParameter('oro_flexibleentity.entities_config');
            $flexibleManagerName = $flexibleConfig['entities_config'][$entityClass]['flexible_manager'];
            $flexibleManager = $this->container->get($flexibleManagerName);

            // 1. get required attributes
            $repo = $flexibleManager->getAttributeRepository();
            $attributes = $repo->findBy(
                array('entityType' => $entityClass, 'required' => true)
            );

            // 2. Check that value is set for any required attributes
            foreach ($attributes as $attribute) {
                if (!$entity->getValueData($attribute->getCode())) {

                    var_dump($entity->getLocaleCode());
                    var_dump($entity->getValueData($attribute->getCode()));
                    var_dump($entity->getValues());

                    throw new HasRequiredValueException('attribute '.$attribute->getCode().' is required');
                }
            }
        }
    }
}
