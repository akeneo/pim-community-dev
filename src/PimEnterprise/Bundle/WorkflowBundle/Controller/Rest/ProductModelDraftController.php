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

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductModelRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Manager\EntityWithValuesDraftManager;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\EntityWithValuesDraftRepository;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse, RedirectResponse, Request
};
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 * Product model draft rest controller
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelDraftController
{
    /** @var ProductModelRepository */
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

    public function __construct(
        ProductModelRepository $productModelRepository,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepository $entityWithValuesDraftRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftManager $manager,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->tokenStorage = $tokenStorage;
        $this->entityWithValuesDraftRepo = $entityWithValuesDraftRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->manager = $manager;
        $this->normalizer = $normalizer;
        $this->userContext = $userContext;
    }

    public function readyAction(Request $request, $productModelId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModel = $this->findProductModelOr404($productModelId);
        $productModelDraft = $this->findDraftForProductModelOr404($productModel);
        $comment = $request->get('comment') ?: null;

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productModelDraft)) {
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
}
