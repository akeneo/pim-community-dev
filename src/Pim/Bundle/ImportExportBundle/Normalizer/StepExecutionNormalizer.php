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
        return [
            'label'     => $this->translator->trans($object->getStepName()),
            'status'    => $this->normalizeStatus($object->getStatus()->getValue()),
            'summary'   => $this->normalizeSummary($object->getSummary()),
            'startedAt' => $this->normalizeDateTime($object->getStartTime()),
            'endedAt'   => $this->normalizeDateTime($object->getEndTime()),
            'warnings'  => $this->normalizeWarnings($object->getWarnings()),
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
     * @param null|\DateTime $datetime
     *
     * @return null|string
     */
    protected function normalizeDateTime($datetime)
    {
        if (!$datetime instanceof \DateTime) {
            return;
        }

        return $datetime->format('Y-m-d H:i:s');
    }

    /**
     * Normalizes the warnings
     *
     * @param Collection $warnings
     *
     * @return array
     */
    protected function normalizeWarnings(Collection $warnings)
    {
        $result = [];
        foreach ($warnings as $warning) {
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
            $result[$this->translator->trans($key)] = $value;
        }

        return $result;
    }

    /**
     * Normalizes the status
     *
     * @param integer $status
     *
     * @return array
     */
    protected function normalizeStatus($status)
    {
        $status = sprintf('pim_import_export.batch_status.%d', $status);

        return $this->translator->trans($status);
    }
}
