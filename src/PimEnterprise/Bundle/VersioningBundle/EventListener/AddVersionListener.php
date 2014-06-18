<?php

namespace PimEnterprise\Bundle\VersioningBundle\EventListener;

use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener as PimAddVersionListener;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Disable the versioning of published product in EE
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddVersionListener extends PimAddVersionListener
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
