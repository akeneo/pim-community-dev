<?php

namespace Pim\Bundle\SearchBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\SearchBundle\Engine\AbstractEngine;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Allow to index product when a value is updated
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexValueListener
{
    /**
     * @var boolean
     */
    protected $realtime;

    /**
     * @var array
     */
    protected $entities;

    /**
     * @var AbstractEngine
     */
    protected $searchEngine;

    /**
     * Unfortunately, can't use AbstractEngine as a parameter here due to circular reference
     *
     * @param ContainerInterface $container The service container
     * @param boolean            $realtime  Realtime update flag
     * @param array              $entities  Entities config array from search.yml
     */
    public function __construct(ContainerInterface $container, $realtime, $entities)
    {
        $this->container = $container;
        $this->realtime  = $realtime;
        $this->entities  = $entities;
    }

    /**
     * @return AbstractEngine
     */
    protected function getSearchEngine()
    {
        if (!$this->searchEngine) {
            $this->searchEngine = $this->container->get('oro_search.search.engine');
        }

        return $this->searchEngine;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof FlexibleValueInterface and $flexible = $entity->getEntity()) {
                if ($this->isSupported($flexible) and $flexible->getId() and !$uow->isScheduledForUpdate($flexible)) {
                    $this->getSearchEngine()->save($flexible, $this->realtime, true);
                }
            }
        }
    }

    /**
     * @param string $entity
     *
     * @return bool
     */
    protected function isSupported($entity)
    {
        return isset($this->entities[get_class($entity)]);
    }
}
