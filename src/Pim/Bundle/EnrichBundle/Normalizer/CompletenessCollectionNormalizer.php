<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Completeness normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCollectionNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($completenesses, $format = null, array $context = array())
    {
        foreach ($completenesses as $locale => $channels) {
            foreach ($channels['channels'] as $channel => $completeness) {
                $completenesses[$locale]['channels'][$channel] = $this->normalizeCompleteness(
                    $completeness,
                    $format,
                    $context
                );
            }
        }

        return $completenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }

    /**
     * Normalize a completeness element
     *
     * @param array  $completeness
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    protected function normalizeCompleteness($completeness, $format = null, array $context = array())
    {
        $missing = [];
        foreach ($completeness['missing'] as $attribute) {
            $missing[] = $attribute->getCode();
        }

        return [
            'missing'      => $missing,
            'completeness' => $this->normalizer->normalize($completeness['completeness'], $format, $context)
        ];
    }
}
