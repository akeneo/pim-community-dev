<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Factory;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobExecutionMessageFactory
{
    private string $jobMessageTypeFallback;

    /**
     * Map the class of the JobExecutionMessageInterface implementation to the job instance type.
     * Example:
     * [
     *      UiJobExecutionMessage::class => ['mass_edit', 'mass_delete'],
     *      ...
     * ]
     */
    private array $mappingJobMessageTypes;

    public function __construct(
        array $mappingJobMessageTypes,
        string $jobMessageTypeFallback
    ) {
        Assert::classExists($jobMessageTypeFallback);
        Assert::subclassOf($jobMessageTypeFallback, JobExecutionMessageInterface::class);
        Assert::allClassExists(array_keys($mappingJobMessageTypes));
        Assert::allSubclassOf(array_keys($mappingJobMessageTypes), JobExecutionMessageInterface::class);

        $this->mappingJobMessageTypes = $mappingJobMessageTypes;
        $this->jobMessageTypeFallback = $jobMessageTypeFallback;
    }

    public function buildFromJobInstance(
        JobInstance $jobInstance,
        int $jobExecutionId,
        array $options
    ): JobExecutionMessageInterface {
        /** @var string|JobExecutionMessageInterface $class */
        $class = $this->getJobMessageClass($jobInstance->getType() ?? '');

        return $class::createJobExecutionMessage($jobExecutionId, $options);
    }

    public function buildFromNormalized(array $normalized, ?string $jobMessageClass): JobExecutionMessageInterface
    {
        /** @var string|JobExecutionMessageInterface $class */
        $class = $jobMessageClass ?? $this->jobMessageTypeFallback;

        Assert::classExists($class);
        Assert::implementsInterface($class, JobExecutionMessageInterface::class);

        return $class::createJobExecutionMessageFromNormalized($normalized);
    }

    private function getJobMessageClass(string $type): string
    {
        foreach ($this->mappingJobMessageTypes as $class => $types) {
            if (in_array($type, $types)) {
                return $class;
            }
        }

        return $this->jobMessageTypeFallback;
    }
}
