<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
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
        $context = array_merge(
            [
                'translationDomain' => 'messages',
                'translationLocale' => 'en_US',
            ],
            $context
        );

        return [
            'label'     => $this->translator->trans(
                $object->getStepName(),
                [],
                $context['translationDomain'],
                $context['translationLocale']
            ),

            'status'    => (string) $object->getStatus(),
            'summary'   => $this->normalizeSummary(
                $object->getSummary(),
                $context['translationDomain'],
                $context['translationLocale']
            ),

            'startedAt' => $this->normalizeDateTime($object->getStartTime()),
            'endedAt'   => $this->normalizeDateTime($object->getEndTime()),

            'warnings'  => array_map(
                function ($warning) use ($context) {
                    return $this->normalizeWarning(
                        $warning,
                        $context['translationDomain'],
                        $context['translationLocale']
                    );
                },
                $object->getWarnings()
            ),

            'failures'  => array_map(
                function ($failure) use ($context) {
                    return $this->translator->trans(
                        $failure['message'],
                        $failure['messageParameters'],
                        $context['translationDomain'],
                        $context['translationLocale']
                    );
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
     * @param null|DateTime $datetime
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
     * Normalizes a warning
     *
     * @param array  $warning
     * @param string $domain
     * @param string $locale
     *
     * @return array
     */
    protected function normalizeWarning(array $warning, $domain, $locale)
    {
        return [
            'label'  => $this->translator->trans($warning['name'], [], $domain, $locale),
            'reason' => $this->translator->trans($warning['reason'], $warning['reasonParameters'], $domain, $locale),
            'item'   => $warning['item'],
        ];
    }

    /**
     * Normalizes the summary
     *
     * @param array  $summary
     * @param string $domain
     * @param string $locale
     *
     * @return array
     */
    protected function normalizeSummary(array $summary, $domain, $locale)
    {
        $result = [];
        foreach ($summary as $key => $value) {
            $result[$this->translator->trans($key, [], $domain, $locale)] = $value;
        }

        return $result;
    }
}
