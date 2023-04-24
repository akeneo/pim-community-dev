<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Application\DeleteJobInstance;

use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\DeleteJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\DeleteJobInstanceHandlerInterface;

final class DeleteJobInstanceHandler implements DeleteJobInstanceHandlerInterface
{
    public function __construct(
        private readonly DeleteJobInstanceInterface $deleteJobInstance
    ) {
    }

    public function handle(DeleteJobInstanceCommand $command): void
    {
        $this->deleteJobInstance->byCodes($command->codes);
    }
}
