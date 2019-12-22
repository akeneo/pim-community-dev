<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Audit\Command;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountCommand
{
    /** @var string */
    private $eventDate;

    public function __construct(string $eventDate)
    {
        $this->eventDate = $eventDate;
    }

    public function eventDate(): string
    {
        return $this->eventDate;
    }
}
