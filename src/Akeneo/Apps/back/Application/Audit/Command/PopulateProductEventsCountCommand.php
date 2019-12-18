<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Audit\Command;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PopulateProductEventsCountCommand
{
    /** @var string */
    private $eventType;
    /** @var string */
    private $eventDate;

    public function __construct(string $eventType, string $eventDate)
    {
        $this->eventType = $eventType;
        $this->eventDate = $eventDate;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function eventDate(): string
    {
        return $this->eventDate;
    }
}
