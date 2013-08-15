<?php

namespace Pim\Bundle\VersioningBundle;

use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    /**
     * Build a version from a versionable entity
     *
     * @param VersionableInterface $versionable
     *
     * @return \Pim\Bundle\VersioningBundle\Entity\Version
     */
    public function build(VersionableInterface $versionable)
    {
        return new Version($versionable);
    }
}
