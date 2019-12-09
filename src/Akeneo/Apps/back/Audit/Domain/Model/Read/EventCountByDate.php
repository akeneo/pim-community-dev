<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Domain\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class EventCountByDate
{
    /** @var int */
    private $eventCount;

    /** @var \Datetime */
    private $eventDate;

    public function __construct(int $eventCount, \DateTime $eventDate)
    {
        $this->eventCount = $eventCount;
        $this->eventDate = $eventDate;
    }

    public function date(): \DateTime
    {
        return $this->eventDate;
    }

    public function count(): int
    {
        return $this->eventCount;
    }
}
