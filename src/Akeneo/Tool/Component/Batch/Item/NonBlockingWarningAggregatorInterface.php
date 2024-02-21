<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Item;

use Akeneo\Tool\Component\Batch\Model\Warning;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface NonBlockingWarningAggregatorInterface
{
    /**
     * Return the non blocking warnings. It flushes the warning, that means if we call this method 2 times
     * in a row the second call will return an empty array.
     *
     * @return Warning[]
     */
    public function flushNonBlockingWarnings(): array;
}
