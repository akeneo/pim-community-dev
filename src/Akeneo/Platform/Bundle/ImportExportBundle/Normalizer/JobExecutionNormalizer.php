<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Normalizer of JobExecution instance
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var NormalizerInterface */
    protected $jobInstanceNormalizer;

    /**
     * @param TranslatorInterface $translator
     * @param NormalizerInterface $jobInstanceNormalizer
     */
    public function __construct(TranslatorInterface $translator, NormalizerInterface $jobInstanceNormalizer)
    {
        $this->translator = $translator;
        $this->jobInstanceNormalizer = $jobInstanceNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot normalize job execution of "%s" because injected serializer is not a normalizer',
                    $object->getLabel()
                )
            );
        }

        return [
            'failures'       => array_map(
                function ($exception) {
                    return $this->translator->trans($exception['message'], $exception['messageParameters']);
                },
                $object->getFailureExceptions()
            ),
            'stepExecutions' => $this->normalizeStepExecutions($object->getStepExecutions(), $format, $context),
            'isRunning'      => $object->isRunning(),
            'status'         => $this->translator->trans(
                sprintf('pim_import_export.batch_status.%d', $object->getStatus()->getValue())
            ),
            'jobInstance'    => $this->jobInstanceNormalizer->normalize($object->getJobInstance(), 'standard', $context)
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
