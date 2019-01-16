<?php

namespace Akeneo\Channel\Bundle\Controller\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Locale rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleController
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $normalizer
     * @param CollectionFilterInterface $collectionFilter
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->localeRepository = $localeRepository;
        $this->normalizer = $normalizer;
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * Get the list of all locales
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $filterLocales = $request->query->getBoolean('filter_locales', true);
        $locales = $request->get('activated', false) ?
            $this->getActivated($filterLocales) : $this->localeRepository->findAll();
        $normalizedLocales = $this->normalizer->normalize($locales, 'internal_api');

        return new JsonResponse($normalizedLocales);
    }

    /**
     * Get activated locales
     *
     * @return mixed
     */
    protected function getActivated(bool $filterLocales)
    {
        $locales = $this->localeRepository->getActivatedLocales();
        $filteredLocales = $filterLocales ? $this->collectionFilter->filterCollection($locales, 'pim.internal_api.locale.view') : $locales;

        return $filteredLocales;
    }
}
