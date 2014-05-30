<?php

namespace PimEnterprise\Bundle\VersioningBundle\EventListener\MongoDBODM;

use Pim\Bundle\VersioningBundle\EventListener\MongoDBODM\AddProductVersionListener as PimAddProductVersionListener;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Disable the versioning of published product in EE
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddProductVersionListener extends PimAddProductVersionListener
{
    /**
     * Mark object as to be versioned
     *
     * @param object $versionable
     */
    protected function addPendingVersioning($versionable)
    {
        if (false === ($versionable instanceof PublishedProductInterface)) {
            parent::addPendingVersioning($versionable);
        }
    }
}
