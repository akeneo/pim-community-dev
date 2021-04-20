<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Filter\FilterExtension;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesUseableInProductGrid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Webmozart\Assert\Assert;

/**
 * Product draft rest controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductDraftController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $repository;

    /** @var EntityWithValuesDraftManager */
    protected $manager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var array */
    protected $supportedReviewActions = ['approve', 'refuse'];

    /** @var SearchableRepositoryInterface */
    protected $attributeSearchableRepository;

    /** @var ConfiguratorInterface */
    private $filtersConfigurator;

    /** @var FilterExtension */
    private $filterExtension;

    /** @var ListAttributesUseableInProductGrid */
    private $attributesUseableInGrid;

    /** @var Client */
    private $elasticSearchClient;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $repository,
        EntityWithValuesDraftManager $manager,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        UserContext $userContext,
        CollectionFilterInterface $collectionFilter,
        SearchableRepositoryInterface $attributeSearchableRepository,
        ListAttributesUseableInProductGrid $attributesUseableInGrid,
        ConfiguratorInterface $filtersConfigurator,
        FilterExtension $filterExtension,
        Client $elasticSearchClient
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->productRepository = $productRepository;
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->userContext = $userContext;
        $this->collectionFilter = $collectionFilter;
        $this->attributeSearchableRepository = $attributeSearchableRepository;
        $this->attributesUseableInGrid = $attributesUseableInGrid;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->filterExtension = $filterExtension;
        $this->elasticSearchClient = $elasticSearchClient;
    }

    /**
     * Mark a product draft as ready
     *
     * @param Request    $request
     * @param int|string $productId
     *
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function readyAction(Request $request, $productId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($productId);
        $productDraft = $this->findDraftForProductOr404($product);
        $comment = $request->get('comment') ?: null;

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft, $comment);

        $normalizationContext = $this->userContext->toArray() + [
            'filter_types'               => ['pim.internal_api.product_value.view'],
            'disable_grouping_separator' => true
        ];


        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Partially approve or refuse an attribute change in a product draft
     *
     * @param Request $request
     * @param mixed   $id
     * @param string  $code
     * @param string  $action  either "approve" or "refuse"
     *
     * @throws NotFoundHttpException
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function partialReviewAction(Request $request, $id, $code, $action)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productDraft = $this->findProductDraftOr404($id);

        if (!in_array($action, $this->supportedReviewActions)) {
            throw new \LogicException(sprintf('"%s" is not a valid review action', $action));
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getEntityWithValue())) {
            throw new AccessDeniedHttpException();
        }

        $channelCode = $request->query->get('scope', null);
        $channel = null !== $channelCode ? $this->findChannelOr404($channelCode) : null;

        $localeCode = $request->query->get('locale', null);
        $locale = null !== $localeCode ? $this->findLocaleOr404($localeCode) : null;

        $attribute = $this->findAttributeOr404($code);

        try {
            $method = $action . 'Change';
            $this->manager->$method(
                $productDraft,
                $attribute,
                $locale,
                $channel,
                ['comment' => $request->query->get('comment')]
            );
            $this->elasticSearchClient->refreshIndex();
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $normalizationContext = $this->userContext->toArray() + [
            'filter_types'               => ['pim.internal_api.product_value.view'],
            'disable_grouping_separator' => true
        ];

        $product = $productDraft->getEntityWithValue();

        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Approve or refuse a product draft
     *
     * @param Request $request
     * @param mixed   $id
     * @param string  $action  either "approve" or "refuse"
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function reviewAction(Request $request, $id, $action)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productDraft = $this->findProductDraftOr404($id);

        if (!in_array($action, $this->supportedReviewActions)) {
            throw new \LogicException(sprintf('"%s" is not a valid review action', $action));
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getEntityWithValue())) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->$action($productDraft, ['comment' => $request->get('comment')]);
            $this->elasticSearchClient->refreshIndex();
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $normalizationContext = $this->userContext->toArray() + [
            'filter_types'               => ['pim.internal_api.product_value.view'],
            'disable_grouping_separator' => true
        ];

        $product = $productDraft->getEntityWithValue();

        return new JsonResponse($this->normalizer->normalize(
            $productDraft->getEntityWithValue(),
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Remove a product draft
     *
     * @param Request $request
     * @param mixed   $id
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productDraft = $this->findProductDraftOr404($id);

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getEntityWithValue())) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->remove($productDraft, [
            'comment' => $request->get('comment')
        ]);

        $normalizationContext = $this->userContext->toArray() + [
            'filter_types'               => ['pim.internal_api.product_value.view'],
            'disable_grouping_separator' => true
        ];

        $product = $productDraft->getEntityWithValue();

        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Get the attribute choice collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function attributeChoiceAction(Request $request)
    {
        $query  = $request->query;
        $search = $query->get('search');
        $options = $query->get('options', []);

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $options['user_groups_ids'] = $user->getGroupsIds();
        $attributes = $this->attributeSearchableRepository->findBySearch($search, $options);

        $normalizedAttributes = [];
        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = ['id' => $attribute->getCode(), 'text' => $attribute->getLabel()];
        }

        return new JsonResponse(['results' => $normalizedAttributes]);
    }

    /**
     * Get the attribute choice collection through the manage filter
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function manageFiltersAttributeChoiceAction(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $search = (string) $request->get('search', '');
        $locale = $request->get('locale', null);
        $user = $this->userContext->getUser();

        if (null == $locale) {
            $locale = $user->getCatalogLocale()->getCode();
        }

        $attributes = $this->attributesUseableInGrid->fetch($locale, $page, $search, $user->getId());
        $attributesAsFilters = empty($attributes) ? [] : $this->formatAttributesAsFilters($attributes);

        return new JsonResponse($attributesAsFilters);
    }

    /**
     * Format a list of attributes as filters using the product-grid configuration
     *
     * @param array $attributes
     *
     * @return array
     */
    private function formatAttributesAsFilters(array $attributes): array
    {
        $configurationAttributes = [];
        foreach ($attributes as $index => $attribute) {
            $attribute['sortOrder'] = $attribute['order'];
            $attribute['useableAsGridFilter'] = true;

            $configurationAttributes[$attribute['code']] = $attribute;
        }

        $gridConfiguration = DatagridConfiguration::createNamed('proposal-grid', [
            ConfiguratorInterface::SOURCE_KEY => [
                ConfiguratorInterface::USEABLE_ATTRIBUTES_KEY => $configurationAttributes
            ],
            FilterConfiguration::FILTERS_KEY => [],
        ]);

        $this->filtersConfigurator->configure($gridConfiguration);

        $gridMetadata = MetadataIterableObject::createNamed('proposal-grid', ['filters' => []]);
        $this->filterExtension->visitMetadata($gridConfiguration, $gridMetadata);

        $attributesAsFilters = $gridMetadata->offsetGet('filters');

        return $attributesAsFilters;
    }

    /**
     * Find a product draft for the current user and specified product
     *
     * @param ProductInterface $product
     *
     * @throws NotFoundHttpException
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function findDraftForProductOr404(ProductInterface $product)
    {
        $username = $this->tokenStorage->getToken()->getUsername();
        $productDraft = $this->repository->findUserEntityWithValuesDraft($product, $username);
        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('Draft for product %s not found', $product->getId()));
        }

        return $productDraft;
    }

    /**
     * Find a product by its id
     *
     * @param mixed $productId
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($productId)
    {
        $product = $this->productRepository->find($productId);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with id %s not found', $productId));
        }

        return $product;
    }

    /**
     * Find a product draft by its id
     *
     * @param mixed $draftId
     *
     * @throws NotFoundHttpException
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function findProductDraftOr404($draftId)
    {
        $productDraft = $this->repository->find($draftId);
        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('Draft with id %s not found', $draftId));
        }

        return $productDraft;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" not found', $code));
        }

        return $attribute;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return ChannelInterface
     */
    protected function findChannelOr404($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new NotFoundHttpException(sprintf('Channel "%s" not found', $code));
        }

        return $channel;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return LocaleInterface
     */
    protected function findLocaleOr404($code)
    {
        $locale = $this->localeRepository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" not found', $code));
        }

        return $locale;
    }
}
