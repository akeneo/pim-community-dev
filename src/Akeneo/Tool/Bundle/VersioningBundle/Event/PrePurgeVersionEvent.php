<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Event;

use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersion;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event sent before a version is about to be purged by the version purger
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrePurgeVersionEvent extends Event
{
    /** @var VersionInterface */
    protected $version;

    public function __construct(PurgeableVersion $version)
    {
        $this->version = $version;
    }

    public function getVersion(): PurgeableVersion
    {
        return $this->version;
    }
}
