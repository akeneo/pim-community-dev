<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Normalizer\Flat;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat locale normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class LocaleNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $localeNormalizer;

    /** @var LocaleAccessManager */
    protected $accessManager;

    /**
     * @param NormalizerInterface $localeNormalizer
     * @param LocaleAccessManager $accessManager
     */
    public function __construct(NormalizerInterface $localeNormalizer, LocaleAccessManager $accessManager)
    {
        $this->localeNormalizer = $localeNormalizer;
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param LocaleInterface $locale
     */
    public function normalize($locale, $format = null, array $context = [])
    {
        $normalizedLocale = $this->localeNormalizer->normalize($locale, $format, $context);

        $normalizedLocale['view_permission'] = implode(
            array_map('strval', $this->accessManager->getViewUserGroups($locale)),
            ','
        );
        $normalizedLocale['edit_permission'] = implode(
            array_map('strval', $this->accessManager->getEditUserGroups($locale)),
            ','
        );

        return $normalizedLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
