<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Completeness normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormat = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($completeness, $format = null, array $context = [])
    {
        return [
            'required'       => $completeness->getRequiredCount(),
            'missing'        => $completeness->getMissingCount(),
            'ratio'          => $completeness->getRatio(),
            'locale'         => $completeness->getLocale()->getCode(),
            'channel_code'   => $completeness->getChannel()->getCode(),
            'channel_labels' => $this->getChannelLabels($completeness->getChannel())
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CompletenessInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * @param ChannelInterface $channel
     *
     * @return array
     */
    protected function getChannelLabels(ChannelInterface $channel)
    {
        $labels = [];
        foreach ($channel->getTranslations() as $translation) {
            $labels[$translation->getLocale()] = null !== $translation->getLabel() ?
                $translation->getLabel() : $channel->getCode();
        }

        return $labels;
    }
}
