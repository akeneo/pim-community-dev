<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SelectEventApiRequestsCountPerDateTime
{
    public function execute(\DateTimeImmutable $dateTime): int;
}
