<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var RemoverInterface */
    protected $remover;

    /** @var EntityRepository */
    protected $attributeRepository;

    /** @var UpdaterInterface */
    protected $attributeUpdater;

    /** @var SaverInterface */
    protected $attributeSaver;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var SimpleFactoryInterface */
    protected $attributeGroupFactory;

    /**
     * @param EntityRepository              $attributeGroupRepo
     * @param SearchableRepositoryInterface $attributeGroupSearchableRepository
     * @param NormalizerInterface           $normalizer
     * @param CollectionFilterInterface     $collectionFilter
     * @param ObjectUpdaterInterface        $updater
     * @param ValidatorInterface            $validator
     * @param SaverInterface                $saver
     * @param RemoverInterface              $remover
     * @param EntityRepository              $attributeRepository
     * @param ObjectUpdaterInterface        $attributeUpdater
     * @param SaverInterface                $attributeSaver
     * @param SecurityFacade                $securityFacade
     * @param SimpleFactoryInterface        $attributeGroupFactory
     */
    public function __construct(
        EntityRepository $attributeGroupRepo,
        SearchableRepositoryInterface $attributeGroupSearchableRepository,
        NormalizerInterface $normalizer,
        CollectionFilterInterface $collectionFilter,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RemoverInterface $remover,
        EntityRepository $attributeRepository,
        ObjectUpdaterInterface $attributeUpdater,
        SaverInterface $attributeSaver,
        SecurityFacade $securityFacade,
        SimpleFactoryInterface $attributeGroupFactory
    ) {
        $this->attributeGroupRepo                 = $attributeGroupRepo;
        $this->attributeGroupSearchableRepository = $attributeGroupSearchableRepository;
        $this->normalizer                         = $normalizer;
        $this->collectionFilter                   = $collectionFilter;
        $this->updater                            = $updater;
        $this->validator                          = $validator;
        $this->saver                              = $saver;
        $this->remover                            = $remover;
        $this->attributeRepository                = $attributeRepository;
        $this->attributeUpdater                   = $attributeUpdater;
        $this->attributeSaver                     = $attributeSaver;
        $this->securityFacade                     = $securityFacade;
        $this->attributeGroupFactory              = $attributeGroupFactory;
    }

    /**
     * Search attribute group collection
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
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

        $applyFilters = $request->request->getBoolean('apply_filters', true);

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

        if ($applyFilters) {
            $attributeGroups = $this->collectionFilter->filterCollection(
                $attributeGroups,
                'pim.internal_api.attribute_group.view'
            );
        }

        $normalizedAttributeGroups = [];

        foreach ($attributeGroups as $attributeGroup) {
            $normalizedAttributeGroups[$attributeGroup->getCode()] = $this->normalizer
                ->normalize($attributeGroup, 'internal_api');
        }

        return new JsonResponse($normalizedAttributeGroups);
    }

    /**
     * Get attribute group collection.
     * We should spilt the search and index action in two controllers to handle rights properly.
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $attributeGroups = $this->attributeGroupRepo->findAll();

        $normalizedAttributeGroups = [];

        foreach ($attributeGroups as $attributeGroup) {
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
     * @AclAncestor("pim_enrich_attributegroup_create")
     */
    public function createAction(Request $request)
    {
        $attributeGroup = $this->attributeGroupFactory->create();

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
        $sortOrder = $data['attributes_sort_order'];
        unset($data['attributes_sort_order']);

        $this->ensureAttributeCollectionRights(
            array_map(function (AttributeInterface $attribute) {
                return $attribute->getCode();
            }, $attributeGroup->getAttributes()->toArray()),
            $data['attributes']
        );

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

        $attributes = $this->attributeRepository->findBy(['code' => array_keys($sortOrder)]);
        foreach ($attributes as $attribute) {
            $data = ['sort_order' => $sortOrder[$attribute->getCode()]];
            $this->attributeUpdater->update($attribute, $data);
            $this->attributeSaver->save($attribute);
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $attributeGroup,
                'internal_api'
            )
        );
    }

    /**
     * Sort the attribute groups
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function sortAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data as $attributeGroupCode => $sortOrder) {
            $attributeGroup = $this->attributeGroupRepo->findOneByIdentifier($attributeGroupCode);
            $this->updater->update($attributeGroup, ['sort_order' => $sortOrder]);
            $this->saver->save($attributeGroup);
        }

        return $this->indexAction();
    }

    /**
     * Remove action
     *
     * @param $code
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attributegroup_remove")
     */
    public function removeAction($identifier)
    {
        $attributeGroup = $this->getAttributeGroupOr404($identifier);

        $this->remover->remove($attributeGroup);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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

    /**
     * Check that the user doesn't change the attribute list without permission
     *
     * @param array $attributeCodesBefore
     * @param array $attributeCodesAfter
     *
     * @throws AccessDeniedHttpException
     */
    protected function ensureAttributeCollectionRights($attributeCodesBefore, $attributeCodesAfter)
    {
        if (!$this->securityFacade->isGranted('pim_enrich_attributegroup_remove_attribute') &&
            count($attributeCodesBefore) > 0 &&
            count(array_diff($attributeCodesBefore, $attributeCodesAfter)) > 0
        ) {
            throw new AccessDeniedHttpException('You cannot remove attributes from the attribute group');
        }

        if (!$this->securityFacade->isGranted('pim_enrich_attributegroup_add_attribute') &&
            count($attributeCodesAfter) > 0 &&
            count(array_diff($attributeCodesAfter, $attributeCodesBefore)) > 0
        ) {
            throw new AccessDeniedHttpException('You cannot add attributes to the attribute group');
        }
    }
}
