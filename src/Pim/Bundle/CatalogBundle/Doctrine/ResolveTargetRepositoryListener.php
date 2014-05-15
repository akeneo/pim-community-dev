<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ResolveTargetRepositoryListener
{
    private $resolveTargetRepositories = array();

    public function addResolveTargetRepository($entity, $newRepository)
    {
        $this->resolveTargetRepositories[ltrim($entity)] = $newRepository;
    }
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $cm = $args->getClassMetadata();
        $className = $cm->getName();
        if (isset($this->resolveTargetRepositories[ltrim($className)])) {
            $cm->customRepositoryClassName = $this->resolveTargetRepositories[ltrim($className)];
        }
    }
}
