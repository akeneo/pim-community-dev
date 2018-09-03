<?php

namespace Pim\Bundle\LocalizationBundle\Controller;

use Akeneo\Tool\Component\Localization\Provider\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Locale controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleController
{
    /** @var LocaleProviderInterface */
    protected $localeProvider;

    /** @var NormalizeInterface */
    protected $normalizer;

    /**
     * @param LocaleProviderInterface $localeProvider
     * @param NormalizerInterface     $normalizer
     */
    public function __construct(LocaleProviderInterface $localeProvider, NormalizerInterface $normalizer)
    {
        $this->localeProvider = $localeProvider;
        $this->normalizer = $normalizer;
    }

    /**
     * Index action (fetch all ui locales)
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        return new JsonResponse($this->normalizer->normalize($this->localeProvider->getLocales(), 'internal_api'));
    }
}
