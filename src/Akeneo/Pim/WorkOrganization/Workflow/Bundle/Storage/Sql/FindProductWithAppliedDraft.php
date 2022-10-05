<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProduct;
use Akeneo\Pim\Permission\Component\Authorization\FetchUserRightsOnProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

final class FindProductWithAppliedDraft implements FindProduct
{
    public function __construct(
        private FindProduct $findProduct,
        private TokenStorageInterface $tokenStorage,
        private FetchUserRightsOnProductInterface $fetchUserRightsOnProduct,
        private EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        private DraftApplierInterface $productDraftApplier
    ) {
    }

    public function withUuid(string $uuid): ?ProductInterface
    {
        $product = $this->findProduct->withUuid($uuid);
        $user = $this->tokenStorage->getToken()?->getUser();
        Assert::isInstanceOf($user, UserInterface::class);
        if (null === $product || null === $user->getId()) {
            return null;
        }

        $userRights = $this->fetchUserRightsOnProduct->fetchByUuid(Uuid::fromString($uuid), $user->getId());
        if ($userRights->canApplyDraftOnProduct()) {
            $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $user->getUserIdentifier());
            if (null !== $productDraft) {
                $this->productDraftApplier->applyAllChanges($product, $productDraft);
            }
        }

        return $product;
    }
}
