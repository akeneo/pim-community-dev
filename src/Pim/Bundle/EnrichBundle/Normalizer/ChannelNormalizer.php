<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Channel normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $channelNormalizer;

    /** @var NormalizerInterface */
    protected $localeNormalizer;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param NormalizerInterface        $channelNormalizer
     * @param NormalizerInterface        $localeNormalizer
     * @param VersionManager             $versionManager
     * @param NormalizerInterface        $versionNormalizer
     * @param CollectionFilterInterface  $collectionFilter
     */
    public function __construct(
        NormalizerInterface $channelNormalizer,
        NormalizerInterface $localeNormalizer,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->channelNormalizer = $channelNormalizer;
        $this->localeNormalizer  = $localeNormalizer;
        $this->versionManager    = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
        $this->collectionFilter  = $collectionFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($channel, $format = null, array $context = [])
    {
        $normalizedChannel = $this->channelNormalizer->normalize($channel, 'standard', $context);

        $normalizedChannel['locales'] = $this->normalizeLocales($channel->getLocales());

        $firstVersion = $this->versionManager->getOldestLogEntry($channel);
        $lastVersion = $this->versionManager->getNewestLogEntry($channel);

        $firstVersion = null !== $firstVersion ?
            $this->versionNormalizer->normalize($firstVersion, 'internal_api') :
            null;
        $lastVersion = null !== $lastVersion ?
            $this->versionNormalizer->normalize($lastVersion, 'internal_api') :
            null;

        $normalizedChannel['meta'] = [
            'id'         => $channel->getId(),
            'form'       => 'pim-channel-edit-form',
            'created'    => $firstVersion,
            'updated'    => $lastVersion,
        ];

        return $normalizedChannel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize and return given $locales
     *
     * @param $locales
     *
     * @return array|\ArrayAccess
     */
    protected function normalizeLocales($locales)
    {
        $normalizedLocales = [];

        foreach ($locales as $locale) {
            $normalizedLocales[] = $this->localeNormalizer->normalize($locale, 'standard');
        }

        return $normalizedLocales;
    }
}
