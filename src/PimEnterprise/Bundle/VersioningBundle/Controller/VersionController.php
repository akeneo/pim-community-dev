<?php

namespace PimEnterprise\Bundle\VersioningBundle\Controller;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Version controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class VersionController extends AbstractDoctrineController
{
    /**
     * @param Version $version
     */
    public function rollbackAction(Version $version)
    {
    }
}
