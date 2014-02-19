<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Normalizer of JobExecution instance
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot normalize job execution of "%s" because injected serializer is not a normalizer',
                    $object->getLabel()
                )
            );
        }

        $context = array_merge(
            [
                'translationDomain' => 'messages',
                'translationLocale' => 'en_US',
            ],
            $context
        );

        return [
            'label' => $object->getLabel(),

            'failures' => array_map(
                function ($exception) use ($context) {
                    return $this->translator->trans(
                        $exception['message'],
                        $exception['messageParameters'],
                        $context['translationDomain'],
                        $context['translationLocale']
                    );
                },
                $object->getFailureExceptions()
            ),

            'stepExecutions' => $this->normalizeStepExecutions($object->getStepExecutions(), $format, $context),

            'isRunning' => $object->isRunning(),
        ];
    }

    /**
     * Normalizes the step executions collection
     *
     * As JobExecution::getStepExecutions() might return something else than an array,
     * (like a PersistentCollection) we use a foreach instead of an array_map
     *
     * @param array|Traversable $stepExecutions
     * @param string            $format
     * @param array             $context
     *
     * @return array
     */
    protected function normalizeStepExecutions($stepExecutions, $format, array $context)
    {
        $result = [];
        foreach ($stepExecutions as $stepExecution) {
            $result[] = $this->serializer->normalize($stepExecution, $format, $context);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobExecution;
    }
}
