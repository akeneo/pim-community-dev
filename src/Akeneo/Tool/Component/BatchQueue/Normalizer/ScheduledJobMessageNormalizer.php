<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Normalizer;

use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ScheduledJobMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct()
    {
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ScheduledJobMessage;
    }

    /**
     * @param ScheduledJobMessageInterface $scheduledJobMessage
     */
    public function normalize($scheduledJobMessage, $format = null, array $context = []): array
    {
        Assert::implementsInterface($scheduledJobMessage, ScheduledJobMessageInterface::class);

        return [
            'job_code' => $scheduledJobMessage->getJobCode(),
            'options' => $scheduledJobMessage->getOptions(),
        ];
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_subclass_of($type, ScheduledJobMessageInterface::class);
    }

    /**
     * @param array $data The normalized ScheduledJobMessage
     */
    public function denormalize(
        $data,
        string $jobMessageClass,
        ?string $format = null,
        array $context = []
    ): ScheduledJobMessageInterface {
        return ScheduledJobMessage::createScheduledJobMessage($data['job_code'], $data['options']);
    }
}
