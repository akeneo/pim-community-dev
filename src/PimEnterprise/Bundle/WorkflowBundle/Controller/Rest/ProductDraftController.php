<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product draft rest controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductDraftController
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ProductDraftManager */
    protected $manager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftManager             $manager
     * @param ProductRepositoryInterface      $productRepository
     * @param NormalizerInterface             $normalizer
     * @param UserContext                     $userContext
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ProductDraftRepositoryInterface $repository,
        ProductDraftManager $manager,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ) {
        $this->securityContext   = $securityContext;
        $this->repository        = $repository;
        $this->manager           = $manager;
        $this->productRepository = $productRepository;
        $this->normalizer        = $normalizer;
        $this->userContext       = $userContext;
    }

    /**
     * @param int|string $id
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $productDraft = $this->findDraftForProduct($id);

        if (null === $productDraft) {
            return new JsonResponse();
        }

        return new JsonResponse($this->normalizer->normalize($productDraft, 'internal_api'));
    }

    /**
     * Mark a product draft as ready
     *
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function readyAction($id)
    {
        if (null === $productDraft = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Product draft "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft);

        return new JsonResponse($this->normalizer->normalize($productDraft, 'internal_api'));
    }

    /**
     * Find a product draft for a product by the product id
     *
     * @param string $id the product id
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft|null
     */
    protected function findDraftForProduct($id)
    {
        $product = $this->productRepository->findOneById($id);

        if ($product) {
            $username = $this->userContext->getUser()->getUsername();
            $productDraft = $this->repository->findUserProductDraft($product, $username);

            return $productDraft;
        }
    }
}
