<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Localization\Presenter\PresenterInterface;
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

    /** @var PresenterInterface */
    protected $presenter;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     * @param PresenterInterface  $presenter
     */
    public function __construct(TranslatorInterface $translator, PresenterInterface $presenter)
    {
        $this->translator = $translator;
        $this->presenter  = $presenter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedWarnings = $this->normalizeWarnings($object->getWarnings(), $context);

        if (isset($context['limit_warnings']) && $object->getWarnings()->count() > 0) {
            $object->addSummaryInfo('displayed', count($normalizedWarnings).'/'.$object->getWarnings()->count());
        }

        return [
            'label'     => $this->translator->trans($object->getStepName()),
            'status'    => $this->normalizeStatus($object->getStatus()->getValue()),
            'summary'   => $this->normalizeSummary($object->getSummary()),
            'startedAt' => $this->presenter->present($object->getStartTime(), $context),
            'endedAt'   => $this->presenter->present($object->getEndTime(), $context),
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
     * Normalizes the warnings
     *
     * @param Collection $warnings
     * @param array      $context
     *
     * @return array
     */
    protected function normalizeWarnings(Collection $warnings, array $context = [])
    {
        $results = [];
        $selectedWarnings = [];

        if (isset($context['limit_warnings']) && $context['limit_warnings'] > 0) {
            $selectedWarnings = $warnings->slice(0, $context['limit_warnings']);
        } else {
            $selectedWarnings = $warnings;
        }

        $items = [];
        foreach ($selectedWarnings as $warning) {
            $parameters = $warning->getReasonParameters();
            $reason = $this->translator->trans($warning->getReason(), $parameters, 'validators');
            if(array_key_exists('attribute', $parameters)) {
                $reason = sprintf('%s: %s: %s',
                    $parameters['attribute'],
                    $this->translator->trans($warning->getReason(), $parameters, 'validators'),
                    $parameters['invalid_value']
                );
            }
            $itemIndex = array_search($warning->getItem(), $items);

            if(false === $itemIndex){
                $items[] = $warning->getItem();
                $results[] = [
                    'label' => $this->translator->trans($warning->getName()),
                    'reasons' => [$reason],
                    'item' => $warning->getItem(),
                ];
            } else {
                $results[$itemIndex]['reasons'][] = $reason;
            }
        }

        return $results;
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
