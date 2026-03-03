<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Job;

use Akeneo\Tool\Bundle\ApiBundle\Handler\DeleteExpiredTokensHandler;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteExpiredTokens implements TaskletInterface
{
    protected StepExecution $stepExecution;

    public function __construct(
        private readonly DeleteExpiredTokensHandler $deleteExpiredTokensHandler,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $this->deleteExpiredTokensHandler->handle();
    }
}
