<?php

namespace Pim\Bundle\VersioningBundle\Event;

use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event sent before a version is processed by the version purger
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PreAdvisementVersionEvent extends Event
{
    /** @var VersionInterface */
    protected $version;

    /**
     * @param VersionInterface $version
     */
    public function __construct(VersionInterface $version)
    {
        $this->version = $version;
    }

    /**
     * @return VersionInterface
     */
    public function getVersion()
    {
        return $this->version;
    }
}
