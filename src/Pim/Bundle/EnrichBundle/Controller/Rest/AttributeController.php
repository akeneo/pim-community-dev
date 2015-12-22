<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var SearchableRepositoryInterface */
    protected $attributeSearchRepository;

    /**
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param NormalizerInterface           $normalizer
     * @param CollectionFilterInterface     $collectionFilter
     * @param SearchableRepositoryInterface $attributeSearchRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter,
        SearchableRepositoryInterface $attributeSearchRepository = null
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->normalizer          = $normalizer;
        $this->collectionFilter    = $collectionFilter;
        $this->attributeSearchRepository = $attributeSearchRepository;
    }

    /**
     * Get the attribute collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $criteria = [];
        if ($request->query->has('identifiers')) {
            $criteria['code'] = explode(',', $request->query->get('identifiers'));
        }
        if ($request->query->has('types')) {
            $criteria['attributeType'] = explode(',', $request->query->get('types'));
        }

        if (null !== $this->attributeSearchRepository) {
            $query  = $request->query;
            $attributes = $this->attributeSearchRepository->findBySearch(
                $query->get('search'),
                $query->get('options', ['limit' => 20])
            );
        } else {
            $attributes = $this->attributeRepository->findBy($criteria);
        }

        $filteredAttributes = $this->collectionFilter
            ->filterCollection($attributes, 'pim.internal_api.attribute.view');
        $normalizedAttributes = $this->normalizer->normalize($filteredAttributes, 'internal_api');

        return new JsonResponse($normalizedAttributes);
    }
}
