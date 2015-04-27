<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute group controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupRestController
{
    /** @var EntityRepository */
    protected $attributeGroupRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param EntityRepository          $attributeGroupRepo
     * @param NormalizerInterface       $normalizer
     * @param CollectionFilterInterface $collectionFilter
     * @param ObjectFilterInterface     $objectFilter
     */
    public function __construct(
        EntityRepository $attributeGroupRepo,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter,
        ObjectFilterInterface $objectFilter
    ) {
        $this->attributeGroupRepo = $attributeGroupRepo;
        $this->normalizer         = $normalizer;
        $this->collectionFilter   = $collectionFilter;
        $this->objectFilter       = $objectFilter;
    }

    /**
     * Get attribute group collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $attributeGroups    = $this->attributeGroupRepo->findAll();
        $filteredAttrGroups = $this->collectionFilter->filterCollection(
            $attributeGroups,
            'pim:internal_api:attribute_group:view'
        );

        $normalizedAttrGroups = [];
        foreach ($filteredAttrGroups as $attributeGroup) {
            $normalizedAttrGroups[$attributeGroup->getCode()] = $this->normalizer->normalize($attributeGroup, 'json');
        }

        return new JsonResponse($normalizedAttrGroups);
    }

    /**
     * get a single attribute group
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $attributeGroup = $this->attributeGroupGroupRepo->findOneById($id);

        if (null === $attributeGroup ||
            (
                null !== $attributeGroup &&
                $this->objectFilter->filterObject($attributeGroup, 'pim:internal_api:attribute_group:view')
            )
        ) {
            throw new NotFoundHttpException(sprintf('No attribute group found for id "%d"', $id));
        }

        return new JsonResponse($this->normalizer->normalize($attributeGroup, 'json'));
    }
}
