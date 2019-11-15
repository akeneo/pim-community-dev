<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Structure\Bundle\Event\AttributeGroupEvents;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /** @var ObjectUpdaterInterface */
    protected $attributeUpdater;

    /** @var SaverInterface */
    protected $attributeSaver;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var SimpleFactoryInterface */
    protected $attributeGroupFactory;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CollectionFilterInterface */
    protected $inputFilter;

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
     * @param EventDispatcherInterface      $eventDispatcher
     * @param CollectionFilterInterface     $inputFilter
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
        SimpleFactoryInterface $attributeGroupFactory,
        EventDispatcherInterface $eventDispatcher,
        CollectionFilterInterface $inputFilter
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
        $this->eventDispatcher                    = $eventDispatcher;
        $this->inputFilter                        = $inputFilter;
    }

    /**
     * Search attribute group collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $applyFilters = $request->request->getBoolean('apply_filters', true);

        $attributeGroups = $this->attributeGroupSearchableRepository
            ->findBySearch(
                $request->request->get('search'),
                $this->parseOptions($request)
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
    public function getAction(string $identifier)
    {
        $attributeGroup = $this->attributeGroupRepo->findOneByIdentifier($identifier);

        if (null === $attributeGroup) {
            throw new NotFoundHttpException(sprintf('Attribute group with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize($attributeGroup, 'internal_api'));
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_attributegroup_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $maxSortOrder = $this->attributeGroupRepo->getMaxSortOrder();
        $attributeGroup = $this->attributeGroupFactory->create();
        $attributeGroup->setSortOrder($maxSortOrder + 1);

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

        $this->eventDispatcher->dispatch(
            AttributeGroupEvents::POST_SAVE,
            new GenericEvent($attributeGroup, ['data' => $data])
        );

        return new JsonResponse(
            $this->normalizer->normalize(
                $attributeGroup,
                'internal_api'
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $identifier
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_attributegroup_edit")
     */
    public function postAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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

        $filteredData = $this->inputFilter->filterCollection(
            $data,
            'pim.internal_api.attribute_group.edit',
            ['preserve_keys' => true]
        );
        $this->updater->update($attributeGroup, $filteredData);

        $violations = $this->validator->validate($attributeGroup);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'internal_api'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($attributeGroup);

        if ($this->securityFacade->isGranted('pim_enrich_attribute_sort')) {
            $attributes = $this->attributeRepository->findBy(['code' => array_keys($sortOrder)]);
            foreach ($attributes as $attribute) {
                $this->attributeUpdater->update($attribute, ['sort_order' => $sortOrder[$attribute->getCode()]]);
                $this->attributeSaver->save($attribute);
            }
        }

        $this->eventDispatcher->dispatch(
            AttributeGroupEvents::POST_SAVE,
            new GenericEvent($attributeGroup, ['data' => $data])
        );

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
     * @AclAncestor("pim_enrich_attributegroup_sort")
     *
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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
     * @param string $identifier
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attributegroup_remove")
     */
    public function removeAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'message' => 'An error occurred.',
                    'global' => true,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $attributeGroup = $this->getAttributeGroupOr404($identifier);

        if ('other' === $attributeGroup->getCode()) {
            return new JsonResponse(
                [
                    'message' => 'Attribute group "other" cannot be removed.'
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (0 < $attributeGroup->getAttributes()->count()) {
            return new JsonResponse(
                ['message' => 'Attribute group containing attributes cannot be removed. Please remove its attributes prior to delete it.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->remover->remove($attributeGroup);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function parseOptions(Request $request): array
    {
        $options = $request->get('options', []);

        if (!isset($options['limit'])) {
            $options['limit'] = SearchableRepositoryInterface::FETCH_LIMIT;
        }

        if (0 > intval($options['limit'])) {
            $options['limit'] = null;
        }

        if (!isset($options['locale'])) {
            $options['locale'] = null;
        }

        if ($request->request->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->request->get('identifiers'));
        }

        if ($request->request->has('attribute_groups')) {
            $options['attribute_groups'] = explode(
                ',',
                $request->request->get('attribute_groups')
            );
        }

        return $options;
    }

    /**
     * Finds attribute group type by identifier or throws not found exception
     *
     * @param string $identifier
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeGroupInterface
     */
    protected function getAttributeGroupOr404(string $identifier): AttributeGroupInterface
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
    protected function ensureAttributeCollectionRights(array $attributeCodesBefore, array $attributeCodesAfter)
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
