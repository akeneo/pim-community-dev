<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\EventSubscriber;

use Pim\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber as BaseAddVersionSubscriber;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Disable the versioning of published product in EE
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AddVersionSubscriber extends BaseAddVersionSubscriber
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
