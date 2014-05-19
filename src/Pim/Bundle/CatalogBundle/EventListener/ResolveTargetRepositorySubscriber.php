<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;

/**
 * Mechanism to overwrite repository class without redefine class mapping
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveTargetRepositorySubscriber implements EventSubscriber
{
    /**
     * @var array
     */
    protected $resolveTargetRepo = array();

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata'
        ];
    }

    /**
     * Adds repository class for a class name
     *
     * @param string $object
     * @param string $newRepository
     */
    public function addResolveTargetRepository($object, $newRepository)
    {
        $this->resolveTargetRepo[ltrim($object)] = $newRepository;
    }

    /**
     * Processes event and resolves new object repository class
     *
     * @param LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();
        $className = $classMetadata->getName();
        if (isset($this->resolveTargetRepo[ltrim($className)])) {
            $classMetadata->customRepositoryClassName = $this->resolveTargetRepo[ltrim($className)];
        }
    }
}
