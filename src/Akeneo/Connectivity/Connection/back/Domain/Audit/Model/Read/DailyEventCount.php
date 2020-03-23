<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DailyEventCount
{
    /** @var string */
    private $date;

    /** @var int */
    private $count;

    public function __construct(string $date, int $count)
    {
        $this->date = $date;
        $this->count = $count;
    }

    public function normalize(): array
    {
        return [$this->date => $this->count];
    }
}
