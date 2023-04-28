<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance;

final class DeleteJobInstanceCommand
{
    /**
     * @param string[] $codes
     */
    public function __construct(
        public readonly array $codes,
    ) {
    }
}
