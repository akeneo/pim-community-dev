<?php

namespace Oro\Bundle\PlatformBundle\Maintenance;

final class Events
{
    /**
     * The maintenance.on event is thrown each time a system locks for a maintenance mode.
     *
     * @var string
     */
    const MAINTENANCE_ON = 'maintenance.on';

    /**
     * The maintenance.off event is thrown each time a system unlocks from a maintenance mode.
     *
     * @var string
     */
    const MAINTENANCE_OFF = 'maintenance.off';
}
