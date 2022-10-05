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

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * If according to user permissions, the product is only editable (so it means it's a draft),
 * returns the product with data from draft applied on it.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DelegatingProductRepository implements IdentifiableObjectRepositoryInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productDraftRepository;

    /** @var DraftApplierInterface */
    private $productDraftApplier;

    /**
     * @param TokenStorageInterface                    $tokenStorage
     * @param AuthorizationCheckerInterface            $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface    $productRepository
     * @param EntityWithValuesDraftRepositoryInterface $productDraftRepository
     * @param DraftApplierInterface                    $productDraftApplier
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        DraftApplierInterface $productDraftApplier
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->productDraftApplier = $productDraftApplier;
        $this->productDraftRepository = $productDraftRepository;
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
}
