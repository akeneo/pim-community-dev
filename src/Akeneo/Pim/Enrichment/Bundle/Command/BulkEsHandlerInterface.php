<?php


namespace Akeneo\Pim\Enrichment\Bundle\Command;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkEsHandlerInterface
{
    public function bulkExecute(array $codes): int;
}
