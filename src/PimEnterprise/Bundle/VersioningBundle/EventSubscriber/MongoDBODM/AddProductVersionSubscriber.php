<?php

namespace PimEnterprise\Bundle\VersioningBundle\EventSubscriber\MongoDBODM;

use Pim\Bundle\VersioningBundle\EventSubscriber\MongoDBODM\AddProductVersionSubscriber
    as BaseAddProductVersionSubscriber;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Disable the versioning of published product in EE
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddProductVersionSubscriber extends BaseAddProductVersionSubscriber
{
    /**
     * {@inheritdoc}
     */
    protected function addPendingVersioning($versionable)
    {
        if (false === ($versionable instanceof PublishedProductInterface)) {
            parent::addPendingVersioning($versionable);
        }
    }
}
