<?php

namespace Akeneo\Channel\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Channel normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $channelNormalizer;

    /** @var NormalizerInterface */
    protected $localeNormalizer;

    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param NormalizerInterface        $channelNormalizer
     * @param NormalizerInterface        $localeNormalizer
     * @param VersionRepositoryInterface $versionRepository
     * @param NormalizerInterface        $versionNormalizer
     * @param CollectionFilterInterface  $collectionFilter
     */
    public function __construct(
        NormalizerInterface $channelNormalizer,
        NormalizerInterface $localeNormalizer,
        VersionRepositoryInterface $versionRepository,
        NormalizerInterface $versionNormalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->channelNormalizer = $channelNormalizer;
        $this->localeNormalizer  = $localeNormalizer;
        $this->versionRepository = $versionRepository;
        $this->versionNormalizer = $versionNormalizer;
        $this->collectionFilter  = $collectionFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($channel, $format = null, array $context = [])
    {
        $normalizedChannel = $this->channelNormalizer->normalize($channel, 'standard', $context);

        $normalizedChannel['locales'] = $this->normalizeLocales($channel->getLocales(), $context['filter_locales'] ?? true);

        $firstVersion = $this->versionRepository->getOldestLogEntry(
            ClassUtils::getClass($channel),
            $channel->getId(),
            false
        );
        $lastVersion = $this->versionRepository->getNewestLogEntry(
            ClassUtils::getClass($channel),
            $channel->getId(),
            false
        );

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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalize and return given $locales
     *
     * @param $locales
     * @param bool $filterLocales
     *
     * @return array|\ArrayAccess
     */
    protected function normalizeLocales($locales, bool $filterLocales)
    {
        $normalizedLocales = [];
        $locales = $filterLocales ? $this->collectionFilter->filterCollection($locales, 'pim.internal_api.locale.view') : $locales;

        foreach ($locales as $locale) {
            $normalizedLocales[] = $this->localeNormalizer->normalize($locale, 'standard');
        }

        return $normalizedLocales;
    }
}
