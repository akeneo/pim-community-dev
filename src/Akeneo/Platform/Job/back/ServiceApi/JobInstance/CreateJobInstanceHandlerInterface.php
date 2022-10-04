<?php

namespace Akeneo\Platform\Job\ServiceApi\JobInstance;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface CreateJobInstanceHandlerInterface
{
    public function handle(CreateJobInstanceCommand $command): void;
}
