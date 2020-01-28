<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener;

/**
 * Disable the versioning of published product in EE.
 * Dirty override of the AddVersionSubscriber :(.
 * We should instead find a proper way to plug our behavior.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class SkipVersionListener extends AddVersionListener
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
