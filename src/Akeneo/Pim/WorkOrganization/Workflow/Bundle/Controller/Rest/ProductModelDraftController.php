<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository\EntityWithValuesDraftRepository;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
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

/**
 * Product model draft rest controller
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelDraftController
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityWithValuesDraftRepository */
    private $entityWithValuesDraftRepo;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var EntityWithValuesDraftManager */
    private $manager;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var UserContext */
    private $userContext;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var array */
    private $supportedReviewActions = ['approve', 'refuse'];

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepository $entityWithValuesDraftRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftManager $manager,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->tokenStorage = $tokenStorage;
        $this->entityWithValuesDraftRepo = $entityWithValuesDraftRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->manager = $manager;
        $this->normalizer = $normalizer;
        $this->userContext = $userContext;
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    public function readyAction(Request $request, $productModelId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModel = $this->findProductModelOr404($productModelId);
        $productModelDraft = $this->findDraftForProductModelOr404($productModel);
        $comment = $request->get('comment') ?: null;

        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productModelDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productModelDraft, $comment);

        $normalizationContext = $this->userContext->toArray() + [
                'filter_types'               => ['pim.internal_api.product_value.view'],
                'disable_grouping_separator' => true
            ];

        return new JsonResponse($this->normalizer->normalize(
            $productModel,
            'internal_api',
            $normalizationContext
        ));
    }

    public function partialReviewAction(Request $request, $id, $code, $action)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModelDraft = $this->findProductModelDraftOr404($id);

        if (!in_array($action, $this->supportedReviewActions)) {
            throw new \LogicException(sprintf('"%s" is not a valid review action', $action));
        }

        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productModelDraft->getEntityWithValue())) {
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
                $productModelDraft,
                $attribute,
                $locale,
                $channel,
                ['comment' => $request->query->get('comment')]
            );
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $normalizationContext = $this->userContext->toArray() + [
                'filter_types'               => ['pim.internal_api.product_value.view'],
                'disable_grouping_separator' => true
            ];

        $productModel = $productModelDraft->getEntityWithValue();

        return new JsonResponse($this->normalizer->normalize(
            $productModel,
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

        $productModelDraft = $this->findProductModelDraftOr404($id);

        if (!in_array($action, $this->supportedReviewActions)) {
            throw new \LogicException(sprintf('"%s" is not a valid review action', $action));
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productModelDraft->getEntityWithValue())) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->$action($productModelDraft, ['comment' => $request->request->get('comment')]);
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $normalizationContext = $this->userContext->toArray() + [
                'filter_types'               => ['pim.internal_api.product_value.view'],
                'disable_grouping_separator' => true,
            ];

        return new JsonResponse($this->normalizer->normalize(
            $productModelDraft->getEntityWithValue(),
            'internal_api',
            $normalizationContext
        ));
    }

    private function findProductModelOr404(string $productModelId): ProductModelInterface
    {
        $productModel = $this->productModelRepository->find($productModelId);
        if (null === $productModel) {
            throw new NotFoundHttpException(sprintf('Product model with id %s not found', $productModelId));
        }

        return $productModel;
    }

    private function findDraftForProductModelOr404(EntityWithValuesInterface $productModel): EntityWithValuesDraftInterface
    {
        $username = $this->tokenStorage->getToken()->getUsername();
        $productModelDraft = $this->entityWithValuesDraftRepo->findUserEntityWithValuesDraft($productModel, $username);
        if (null === $productModelDraft) {
            throw new NotFoundHttpException(sprintf('Draft for product model %s not found', $productModel->getId()));
        }

        return $productModelDraft;
    }

    private function findProductModelDraftOr404(string $draftId): EntityWithValuesDraftInterface
    {
        $productModelDraft = $this->entityWithValuesDraftRepo->find($draftId);
        if (null === $productModelDraft) {
            throw new NotFoundHttpException(sprintf('Draft with id %s not found', $draftId));
        }

        return $productModelDraft;
    }

    private function findAttributeOr404(string $code): AttributeInterface
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" not found', $code));
        }

        return $attribute;
    }

    private function findChannelOr404(string $code): ChannelInterface
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new NotFoundHttpException(sprintf('Channel "%s" not found', $code));
        }

        return $channel;
    }

    private function findLocaleOr404(string $code): LocaleInterface
    {
        $locale = $this->localeRepository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" not found', $code));
        }

        return $locale;
    }
}
