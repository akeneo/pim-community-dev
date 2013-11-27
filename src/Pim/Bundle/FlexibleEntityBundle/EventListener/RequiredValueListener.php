<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;
use Pim\Bundle\FlexibleEntityBundle\Exception\HasRequiredValueException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define required value behavior, throw exception if value related to required attribute is not defined
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequiredValueListener implements EventSubscriber
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
     * @return RequiredValueListener
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
            'preUpdate'
        );
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     *
     * @throws HasRequiredValueException
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
     * @throws HasRequiredValueException
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->checkRequired($args);
    }

    /**
     * Check if all values required are set
     * @param LifecycleEventArgs $args
     *
     * @throws HasRequiredValueException
     */
    protected function checkRequired(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        // check entity implements "has required value" behavior
        if ($entity instanceof FlexibleInterface) {
            // get flexible config
            $entityClass = ClassUtils::getRealClass(get_class($entity));
            $metadata = $args->getEntityManager()->getClassMetadata($entityClass);

            $flexibleConfig = $this->container->getParameter('pim_flexibleentity.flexible_config');
            if (!$metadata->isMappedSuperclass && array_key_exists($entityClass, $flexibleConfig['entities_config'])) {
                $flexibleManagerName = $flexibleConfig['entities_config'][$entityClass]['flexible_manager'];
                $flexibleManager = $this->container->get($flexibleManagerName);

                // get required attributes
                $repo = $flexibleManager->getAttributeRepository();
                $attributes = $repo->findBy(array('entityType' => $entityClass, 'required' => true));
                // check that value is set for any required attributes
                foreach ($attributes as $attribute) {
                    if (!$entity->getValue($attribute->getCode())) {
                        throw new HasRequiredValueException('attribute '.$attribute->getCode().' is required');
                    }
                }
            }
        }
    }
}
