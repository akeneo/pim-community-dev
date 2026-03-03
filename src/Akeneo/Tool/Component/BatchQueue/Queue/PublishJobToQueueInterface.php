<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

interface PublishJobToQueueInterface
{
    public function publish(string $jobInstanceCode, array $config, bool $noLog = false, ?string $username = null, ?array $emails = []): JobExecution;
}
