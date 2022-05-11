<?php

namespace Akeneo\Channel\Infrastructure\Controller\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
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
    public function __construct(
        private LocaleRepositoryInterface $localeRepository,
        private NormalizerInterface $normalizer,
        private CollectionFilterInterface $collectionFilter
    ) {
    }

    /**
     * Get the list of all locales
     */
    public function indexAction(Request $request): JsonResponse
    {
        $filterLocales = $request->query->getBoolean('filter_locales', true);
        $locales = $request->get('activated', false) ?
            $this->getActivated($filterLocales) : $this->localeRepository->findAll();
        $normalizedLocales = $this->normalizer->normalize($locales, 'internal_api');

        return new JsonResponse($normalizedLocales);
    }

    /**
     * Get activated locales
     */
    private function getActivated(bool $filterLocales): mixed
    {
        $locales = $this->localeRepository->getActivatedLocales();
        $filteredLocales = $filterLocales ? $this->collectionFilter->filterCollection($locales, 'pim.internal_api.locale.view') : $locales;

        return $filteredLocales;
    }
}
