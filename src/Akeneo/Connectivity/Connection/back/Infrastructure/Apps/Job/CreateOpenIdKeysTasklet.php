<?php

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Job;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Create or update a public/private key couple to be used to sign openid token and store it into database
 */
final class CreateOpenIdKeysTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'create_openid_keys';

    public function __construct(private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());
    }
}
