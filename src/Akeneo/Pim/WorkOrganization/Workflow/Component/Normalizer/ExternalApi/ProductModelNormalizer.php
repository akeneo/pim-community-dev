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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Add metadata to product model normalized data concerning only the PIM Enterprise Edition
 */
class ProductModelNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const WORKFLOW_STATUS_WORKING_COPY = 'working_copy';
    const WORKFLOW_STATUS_READ_ONLY = 'read_only';
    const WORKFLOW_STATUS_IN_PROGRESS = 'draft_in_progress';
    const WORKFLOW_STATUS_WAITING_FOR_APPROVAL = 'proposal_waiting_for_approval';

    /** @var NormalizerInterface */
    protected $productModelNormalizer;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(
        NormalizerInterface $productModelNormalizer,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->productModelNormalizer = $productModelNormalizer;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        $normalizedProduct = $this->productModelNormalizer->normalize($productModel, $format, $context);
        $normalizedProduct['metadata']['workflow_status'] = $this->determineWorkflowStatus($productModel);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->productModelNormalizer->supportsNormalization($data, $format);
    }


    public function hasCacheableSupportsMethod(): bool
    {
        return $this->productModelNormalizer instanceof CacheableSupportsMethodInterface
            && $this->productModelNormalizer->hasCacheableSupportsMethod();
    }

    /**
     * Determine the workflow status of a product model according to the user permissions.
     *
     * @throws \LogicException If the user has not even the "view" permission on the product.
     */
    private function determineWorkflowStatus(ProductModelInterface $productModel): string
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);

        if ($isOwner) {
            return static::WORKFLOW_STATUS_WORKING_COPY;
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if ($canEdit) {
            $userName = $this->tokenStorage->getToken()->getUserIdentifier();
            $productDraft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($productModel, $userName);

            if (null === $productDraft) {
                return static::WORKFLOW_STATUS_WORKING_COPY;
            }

            Assert::implementsInterface($productDraft, EntityWithValuesDraftInterface::class);
            if (EntityWithValuesDraftInterface::READY === $productDraft->getStatus()) {
                return static::WORKFLOW_STATUS_WAITING_FOR_APPROVAL;
            }

            return static::WORKFLOW_STATUS_IN_PROGRESS;
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $productModel);

        if ($canView) {
            return static::WORKFLOW_STATUS_READ_ONLY;
        }

        throw new \LogicException('A product model should not be normalized if the user has not the "view" permission on it.');
    }
}
