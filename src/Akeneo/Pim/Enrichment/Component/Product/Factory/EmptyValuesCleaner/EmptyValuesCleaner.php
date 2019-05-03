<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

/**
 * Interface to remove values that are empty. No need to store them or instantiate them.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EmptyValuesCleaner
{
    public function clean(OnGoingCleanedRawValues $onGoingCleanedRawValues): OnGoingCleanedRawValues;
}
