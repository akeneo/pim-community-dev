<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Event;

use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent before a version is processed by the version purger
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PreAdvisementVersionEvent extends Event
{
    /** @var PurgeableVersionList */
    protected $version;

    public function __construct(PurgeableVersionList $version)
    {
        $this->version = $version;
    }

    public function getVersion(): PurgeableVersionList
    {
        return $this->version;
    }
}
