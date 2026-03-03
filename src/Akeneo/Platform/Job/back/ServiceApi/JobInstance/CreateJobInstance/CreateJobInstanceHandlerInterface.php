<?php

namespace Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance;

use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface CreateJobInstanceHandlerInterface
{
    /**
     * @throws CannotCreateJobInstanceException
     * @throws InvalidJobException
     */
    public function handle(CreateJobInstanceCommand $command): void;
}
