<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute group controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupController
{
    /** @var EntityRepository */
    protected $attributeGroupRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param EntityRepository          $attributeGroupRepo
     * @param NormalizerInterface       $normalizer
     * @param CollectionFilterInterface $collectionFilter
     */
    public function __construct(
        EntityRepository $attributeGroupRepo,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->attributeGroupRepo = $attributeGroupRepo;
        $this->normalizer = $normalizer;
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * Get attribute group collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $attributeGroups = $this->attributeGroupRepo->findAll();
        $filteredAttrGroups = $this->collectionFilter->filterCollection(
            $attributeGroups,
            'pim.internal_api.attribute_group.view'
        );

        $normalizedAttrGroups = [];
        foreach ($filteredAttrGroups as $attributeGroup) {
            $normalizedAttrGroups[$attributeGroup->getCode()] = $this->normalizer->normalize($attributeGroup, 'standard');
        }

        return new JsonResponse($normalizedAttrGroups);
    }
}
