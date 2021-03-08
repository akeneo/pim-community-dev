<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Normalizer;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
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
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobExecutionMessage;
    }

    public function normalize($jobExecutionMessage, $format = null, array $context = []): array
    {
        Assert::isInstanceOf($jobExecutionMessage, JobExecutionMessage::class);

        return [
            'id' => $jobExecutionMessage->getId(),
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            'consumer' => $jobExecutionMessage->getConsumer(),
            'created_time' => $jobExecutionMessage->getCreateTime()->format('c'),
            'updated_time' => null !== $jobExecutionMessage->getUpdatedTime() ?
                $jobExecutionMessage->getUpdatedTime()->format('c')
                : null,
            'options' => $jobExecutionMessage->getOptions(),
        ];
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === JobExecutionMessage::class;
    }

    public function denormalize($data, $type, $format = null, array $context = []): JobExecutionMessage
    {
        return JobExecutionMessage::createJobExecutionMessageFromNormalized(
            $data['id'],
            $data['job_execution_id'],
            $data['consumer'],
            new \DateTime($data['created_time']),
            null !== $data['updated_time'] ? new \DateTime($data['updated_time']) : null,
            $data['options'],
        );
    }
}
