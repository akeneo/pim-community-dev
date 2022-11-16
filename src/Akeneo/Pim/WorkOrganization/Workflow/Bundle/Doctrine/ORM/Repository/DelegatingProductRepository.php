<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

/**
 * If according to user permissions, the product is only editable (so it means it's a draft),
 * returns the product with data from draft applied on it.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DelegatingProductRepository implements IdentifiableObjectRepositoryInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
        private IdentifiableObjectRepositoryInterface $productRepository,
        private EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        private DraftApplierInterface $productDraftApplier
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['identifier'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            return null;
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($canEdit && !$isOwner) {
            $username = $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
            $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $username);
            if (null !== $productDraft) {
                $this->productDraftApplier->applyAllChanges($product, $productDraft);
            }
        }

        return $product;
    }

    public function findOneByUuid(UuidInterface $uuid): ?ProductInterface
    {
        Assert::methodExists($this->productRepository, 'findOneByUuid');
        $product = $this->productRepository->findOneByUuid($uuid);
        if (null === $product) {
            return null;
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($canEdit && !$isOwner) {
            $username = $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
            $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $username);
            if (null !== $productDraft) {
                $this->productDraftApplier->applyAllChanges($product, $productDraft);
            }
        }

        return $product;
    }
}
