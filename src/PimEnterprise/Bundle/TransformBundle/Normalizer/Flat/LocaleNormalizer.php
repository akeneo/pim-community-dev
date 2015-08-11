<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat locale normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class LocaleNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

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
        $this->accessManager    = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($locale, $format = null, array $context = [])
    {
        $normalizedLocale = $this->localeNormalizer->normalize($locale, $format, $context);

        if (true === $context['versioning']) {
            $normalizedLocale['view_permission'] = implode(
                array_map('strval', $this->accessManager->getViewUserGroups($locale)),
                ','
            );
            $normalizedLocale['edit_permission'] = implode(
                array_map('strval', $this->accessManager->getEditUserGroups($locale)),
                ','
            );
        }

        return $normalizedLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && in_array($format, $this->supportedFormats);
    }
}
