<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PurgeAuditProductQuery
{
    /**
     * Returns the number of rows deleted.
     *
     * @param \DateTimeImmutable $before Delete rows that have been saved strictly before this datetime.
     *
     * @return int
     */
    public function execute(\DateTimeImmutable $before): int;
}
