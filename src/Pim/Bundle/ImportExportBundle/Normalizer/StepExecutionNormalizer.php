<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Normalizer of StepExecution instance
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepExecutionNormalizer implements NormalizerInterface
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedWarnings = $this->normalizeWarnings($object->getWarnings(), $context);

        if (isset($context['limit_warnings']) && $object->getWarnings()->count() > 0) {
            $object->addSummaryInfo('displayed', count($normalizedWarnings).'/'.$object->getWarnings()->count());
        }

        return [
            'label'     => $this->translator->trans($object->getStepName()),
            'status'    => $this->normalizeStatus($object->getStatus()->getValue()),
            'summary'   => $this->normalizeSummary($object->getSummary()),
            'startedAt' => $this->normalizeDateTime($object->getStartTime()),
            'endedAt'   => $this->normalizeDateTime($object->getEndTime()),
            'warnings'  => $normalizedWarnings,
            'failures'  => array_map(
                function ($failure) {
                    return $this->translator->trans($failure['message'], $failure['messageParameters']);
                },
                $object->getFailureExceptions()
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

    /**
     * Normalizes DateTime object
     *
     * @param null|\Datetime $utcDatetime
     *
     * @return null|string
     */
    protected function normalizeDateTime($utcDatetime)
    {
        if (!$utcDatetime instanceof \DateTime) {
            return;
        }

        $datetime = new \DateTime();

        $datetime->setTimestamp($utcDatetime->getTimestamp());

        return $datetime->format('Y-m-d g:i:s A');
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
            $result[] =  [
                'label'  => $this->translator->trans($warning->getName()),
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
