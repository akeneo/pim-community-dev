<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Build a new version
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BuildVersionEvent extends Event
{
    /** @var string */
    protected $username;

    /**
     * @param string $username
     *
     * @return BuildVersionEvent
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}
