<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\StoppableJobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Normalizer of JobExecution instance.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    protected TranslatorInterface $translator;
    protected NormalizerInterface $jobInstanceNormalizer;
    private JobRegistry $jobRegistry;

    public function __construct(
        TranslatorInterface $translator,
        NormalizerInterface $jobInstanceNormalizer,
        JobRegistry $jobRegistry
    ) {
        $this->translator = $translator;
        $this->jobInstanceNormalizer = $jobInstanceNormalizer;
        $this->jobRegistry = $jobRegistry;
    }

    public function normalize($jobExecution, $format = null, array $context = [])
    {
        $jobInstance = $jobExecution->getJobInstance();
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $isRunning = $jobExecution->isRunning();
        $isStoppable = $isRunning && $job instanceof StoppableJobInterface && $job->isStoppable();

        return [
            'failures' => array_map(
                function ($exception) {
                    return $this->translator->trans($exception['message'], $exception['messageParameters']);
                },
                $jobExecution->getFailureExceptions()
            ),
            'stepExecutions' => $this->normalizeStepExecutions($jobExecution->getStepExecutions(), $format, $context),
            'isRunning' => $isRunning,
            'isStoppable' => $isStoppable,
            'status' => $this->translator->trans(
                sprintf('pim_import_export.batch_status.%d', $jobExecution->getStatus()->getValue())
            ),
            'jobInstance' => $this->jobInstanceNormalizer->normalize($jobInstance, 'standard', $context),
        ];
    }

    /**
     * Normalizes the step executions collection.
     *
     * As JobExecution::getStepExecutions() might return something else than an array,
     * (like a PersistentCollection) we use a foreach instead of an array_map
     *
     * @param array  $stepExecutions
     * @param string $format
     *
     * @return array
     */
    protected function normalizeStepExecutions($stepExecutions, $format, array $context)
    {
        $result = [];
        foreach ($stepExecutions as $stepExecution) {
            $result[] = $this->normalizer->normalize($stepExecution, $format, $context);
        }

        return $result;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof JobExecution;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
