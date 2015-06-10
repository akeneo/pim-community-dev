<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRestController
{
    /** @var EntityRepository */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param EntityRepository          $attributeRepository
     * @param NormalizerInterface       $normalizer
     * @param CollectionFilterInterface $collectionFilter
     */
    public function __construct(
        EntityRepository $attributeRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->normalizer          = $normalizer;
        $this->collectionFilter    = $collectionFilter;
    }

    /**
     * Get the attribute collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $attributes = $this->attributeRepository->findAll();
        $filteredAttributes = $this->collectionFilter->filterCollection($attributes, 'pim.internal_api.attribute.view');
        $normalizedAttributes = $this->normalizer->normalize($filteredAttributes, 'internal_api');

        return new JsonResponse($normalizedAttributes);
    }
}
