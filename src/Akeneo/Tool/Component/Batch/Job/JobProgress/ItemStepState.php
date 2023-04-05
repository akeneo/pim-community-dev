<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Job\JobProgress;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ItemStepState
{
    public function __construct(
        private readonly ItemReaderState $itemReaderState,
        private readonly ItemWriterState $itemWriterState,
    ) {
    }
}
