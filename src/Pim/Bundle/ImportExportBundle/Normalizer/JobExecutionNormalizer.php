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
            'label' => $object->getLabel(),

            'failureExceptions' => array_map(
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

            'stepExecutions' => array_map(
                function ($stepExecution) use ($format) {
                    return $this->serializer->normalize($stepExecution, $format);
                },
                $object->getStepExecutions()
            ),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobExecution;
    }
}
