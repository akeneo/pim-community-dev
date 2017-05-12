<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

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

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /**
     * @param EntityRepository              $attributeGroupRepo
     * @param SearchableRepositoryInterface $attributeGroupSearchableRepository
     * @param NormalizerInterface           $normalizer
     * @param CollectionFilterInterface     $collectionFilter
     * @param ObjectUpdaterInterface        $updater
     * @param ValidatorInterface            $validator
     * @param SaverInterface                $saver
     */
    public function __construct(
        EntityRepository $attributeGroupRepo,
        SearchableRepositoryInterface $attributeGroupSearchableRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver
    ) {
        $this->attributeGroupRepo = $attributeGroupRepo;
        $this->attributeGroupSearchableRepository = $attributeGroupSearchableRepository;
        $this->normalizer = $normalizer;
        $this->collectionFilter = $collectionFilter;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
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
                ->normalize($attributeGroup, 'internal_api');
        }

        return new JsonResponse($normalizedAttributeGroups);
    }

    /**
     * Get a single attribute group
     *
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $attributeGroup = $this->attributeGroupRepo->findOneByIdentifier($identifier);

        if (null === $attributeGroup) {
            throw new NotFoundHttpException(sprintf('Attribute group with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize($attributeGroup, 'internal_api'));
    }

    /**
     * @param Request $request
     * @param string $identifier
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attributegroup_edit")
     */
    public function postAction(Request $request, $identifier)
    {
        $attributeGroup = $this->getAttributeGroupOr404($identifier);

        $data = json_decode($request->getContent(), true);
        $this->updater->update($attributeGroup, $data);

        $violations = $this->validator->validate($attributeGroup);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'internal_api'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($attributeGroup);

        return new JsonResponse(
            $this->normalizer->normalize(
                $attributeGroup,
                'internal_api'
            )
        );
    }

    /**
     * Finds attribute group type by identifier or throws not found exception
     *
     * @param $identifier
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeGroupInterface
     */
    protected function getAttributeGroupOr404($identifier)
    {
        $attributeGroup = $this->attributeGroupRepo->findOneByIdentifier($identifier);
        if (null === $attributeGroup) {
            throw new NotFoundHttpException(
                sprintf('Attribute group with identifier "%s" not found', $identifier)
            );
        }

        return $attributeGroup;
    }
}
