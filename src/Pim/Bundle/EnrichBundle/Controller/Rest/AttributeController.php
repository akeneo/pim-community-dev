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
        $options = [];
        if ($request->query->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->query->get('identifiers'));
        }
        if ($request->query->has('types')) {
            $options['attributeType'] = explode(',', $request->query->get('types'));
        }
        if (empty($options)) {
            $options = $request->query->get('options', ['limit' => 20]);
        }

        if (null !== $this->attributeSearchRepository) {
            $attributes = $this->attributeSearchRepository->findBySearch(
                $request->query->get('search'),
                $options
            );
        } else {
            if (isset($options['identifiers'])) {
                $options['code'] = $options['identifiers'];
            }
            $attributes = $this->attributeRepository->findBy($options);
        }

        $filteredAttributes = $this->collectionFilter
            ->filterCollection($attributes, 'pim.internal_api.attribute.view');
        $normalizedAttributes = $this->normalizer->normalize($filteredAttributes, 'internal_api');

        return new JsonResponse($normalizedAttributes);
    }
}
