<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\IsUserCategoryGranted;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AclIsUserCategoryGranted implements IsUserCategoryGranted
{
    public function __construct(
        /* @phpstan-ignore-next-line */
        private ?ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function forProductAndAccessLevel(
        int $userId,
        ProductIdentifier $productIdentifier,
        string $accessLevel
    ): bool {
        Assert::notNull($this->productCategoryAccessQuery);

        $user = $this->userRepository->findOneBy(['id' => $userId]);
        Assert::notNull($user);
        Assert::implementsInterface($user, UserInterface::class);

        /* @phpstan-ignore-next-line */
        $grantedIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            [$productIdentifier->asString()],
            $user->getGroupsIds(),
            $accessLevel
        );

        return [] !== $grantedIdentifiers;
    }
}
