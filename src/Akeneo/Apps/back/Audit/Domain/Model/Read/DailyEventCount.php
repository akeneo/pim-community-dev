<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Domain\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DailyEventCount
{
    /** @var int */
    private $count;

    /** @var \Datetime */
    private $date;

    public function __construct(int $count, \DateTime $date)
    {
        $this->count = $count;
        $this->date = $date;
    }

    public function normalize(): array
    {
        return [
            'date' => $this->date->format('Y-m-d'),
            'value' => $this->count
        ];
    }
}
