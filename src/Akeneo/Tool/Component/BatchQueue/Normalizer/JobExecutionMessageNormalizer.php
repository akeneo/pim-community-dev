<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Normalizer;

use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(private JobExecutionMessageFactory $jobExecutionMessageFactory)
    {
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof JobExecutionMessageInterface;
    }

    /**
     * @param JobExecutionMessageInterface $jobExecutionMessage
     */
    public function normalize($jobExecutionMessage, $format = null, array $context = []): array
    {
        Assert::implementsInterface($jobExecutionMessage, JobExecutionMessageInterface::class);

        return [
            'id' => $jobExecutionMessage->getId()->toString(),
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            'created_time' => $jobExecutionMessage->getCreateTime()->format('c'),
            'updated_time' => null !== $jobExecutionMessage->getUpdatedTime() ?
                $jobExecutionMessage->getUpdatedTime()->format('c')
                : null,
            'options' => $jobExecutionMessage->getOptions(),
        ];
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_subclass_of($type, JobExecutionMessageInterface::class);
    }

    /**
     * @param array $data The normalized JobExecution message
     */
    public function denormalize(
        $data,
        string $jobMessageClass,
        ?string $format = null,
        array $context = []
    ): JobExecutionMessageInterface {
        return $this->jobExecutionMessageFactory->buildFromNormalized($data, $jobMessageClass);
    }
}
