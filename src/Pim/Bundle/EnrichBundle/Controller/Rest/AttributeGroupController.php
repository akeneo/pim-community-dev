<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute group controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupController
{
    /** @var EntityRepository */
    protected $attributeGroupRepo;

    /** @var SearchableRepositoryInterface */
    protected $attributeGroupSearchableRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param EntityRepository              $attributeGroupRepo
     * @param SearchableRepositoryInterface $attributeGroupSearchableRepository
     * @param NormalizerInterface           $normalizer
     * @param CollectionFilterInterface     $collectionFilter
     */
    public function __construct(
        EntityRepository $attributeGroupRepo,
        SearchableRepositoryInterface $attributeGroupSearchableRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->attributeGroupRepo = $attributeGroupRepo;
        $this->attributeGroupSearchableRepository = $attributeGroupSearchableRepository;
        $this->normalizer = $normalizer;
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * Get attribute group collection
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $options = [];

        if ($request->request->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->request->get('identifiers'));
        }

        if ($request->request->has('attribute_groups')) {
            $options['attribute_groups'] = explode(
                ',',
                $request->request->get('attribute_groups')
            );
        }

        if (empty($options)) {
            $options = $request->request->get(
                'options',
                [
                    'limit' => SearchableRepositoryInterface::FETCH_LIMIT,
                    'locale' => null,
                ]
            );
        }

        $attributeGroups = $this->attributeGroupSearchableRepository
            ->findBySearch(
                $request->request->get('search'),
                $options
            );

        $filteredAttributeGroups = $this->collectionFilter->filterCollection(
            $attributeGroups,
            'pim.internal_api.attribute_group.view'
        );

        $normalizedAttributeGroups = [];

        foreach ($filteredAttributeGroups as $attributeGroup) {
            $normalizedAttributeGroups[$attributeGroup->getCode()] = $this->normalizer
                ->normalize($attributeGroup, 'standard');
        }

        return new JsonResponse($normalizedAttributeGroups);
    }
}
