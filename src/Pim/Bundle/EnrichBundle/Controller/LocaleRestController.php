<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Locale rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleRestController
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $normalizer
     */
    public function __construct(LocaleRepositoryInterface $localeRepository, NormalizerInterface $normalizer)
    {
        $this->localeRepository = $localeRepository;
        $this->normalizer       = $normalizer;
    }

    /**
     * Get the list of all locales
     *
     * @return JsonResponse all activated locales
     */
    public function indexAction()
    {
        $locales = $this->localeRepository->getActivatedLocales();

        $normalizedLocales = [];
        foreach ($locales as $locale) {
            $normalizedLocales[] = $this->normalizer->normalize($locale, 'internal_api');
        }

        return new JsonResponse($normalizedLocales);
    }
}
