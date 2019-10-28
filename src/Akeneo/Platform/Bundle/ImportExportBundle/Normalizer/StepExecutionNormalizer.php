<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Normalizer of StepExecution instance
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepExecutionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var PresenterInterface */
    protected $presenter;

    /**
     * @param TranslatorInterface $translator
     * @param PresenterInterface  $presenter
     */
    public function __construct(
        TranslatorInterface $translator,
        PresenterInterface $presenter
    ) {
        $this->translator = $translator;
        $this->presenter = $presenter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($stepExecution, $format = null, array $context = [])
    {
        $normalizedWarnings = $this->normalizeWarnings($stepExecution->getWarnings(), $context);

        if (isset($context['limit_warnings']) && $stepExecution->getWarnings()->count() > 0) {
            $stepExecution->addSummaryInfo('displayed', count($normalizedWarnings).'/'.$stepExecution->getWarnings()->count());
        }

        return [
            'label'     => $stepExecution->getStepName(),
            'job'       => $stepExecution->getJobExecution()->getJobInstance()->getJobName(),
            'status'    => $this->normalizeStatus($stepExecution->getStatus()->getValue()),
            'summary'   => $this->normalizeSummary($stepExecution->getSummary()),
            'startedAt' => $this->presenter->present($stepExecution->getStartTime(), $context),
            'endedAt'   => $this->presenter->present($stepExecution->getEndTime(), $context),
            'warnings'  => $normalizedWarnings,
            'failures'  => array_map(
                function ($failure) {
                    return $this->translator->trans($failure['message'], $failure['messageParameters']);
                },
                $stepExecution->getFailureExceptions()
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof StepExecution;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalizes the warnings
     *
     * @param Collection $warnings
     * @param array      $context
     *
     * @return array
     */
    protected function normalizeWarnings(Collection $warnings, array $context = [])
    {
        $result = [];
        $selectedWarnings = [];

        if (isset($context['limit_warnings']) && $context['limit_warnings'] > 0) {
            $selectedWarnings = $warnings->slice(0, $context['limit_warnings']);
        } else {
            $selectedWarnings = $warnings;
        }

        foreach ($selectedWarnings as $warning) {
            $result[] = [
                'reason' => $this->translator->trans($warning->getReason(), $warning->getReasonParameters()),
                'item'   => $warning->getItem(),
            ];
        }

        return $result;
    }

    /**
     * Normalizes the summary
     *
     * @param array $summary
     *
     * @return array
     */
    protected function normalizeSummary(array $summary)
    {
        $result = [];
        foreach ($summary as $key => $value) {
            $key = sprintf('job_execution.summary.%s', $key);
            $result[$this->translator->trans($key)] = $this->translator->trans($value);
        }

        return $result;
    }

    /**
     * Normalizes the status
     *
     * @param int $status
     *
     * @return string
     */
    protected function normalizeStatus($status)
    {
        $status = sprintf('pim_import_export.batch_status.%d', $status);

        return $this->translator->trans($status);
    }
}
