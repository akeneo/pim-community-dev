<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface as LegacyProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidQueryFetcher;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandler
{
    public function __construct(
        private ProductQueryBuilderInterface $pqb,
        private ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        private ProductUuidQueryFetcher $productUuidQueryFetcher,
        private ValidatorInterface $validator,
        private FeatureFlags $featureFlags,
        private UserRepositoryInterface $userRepository,
        /* @phpstan-ignore-next-line */
        private ?GetGrantedCategoryCodes $getGrantedCategoryCodes
    ) {
    }

    public function __invoke(GetProductUuidsQuery $getProductUuidsQuery): ProductUuidCursorInterface
    {
        $violations = $this->validator->validate($getProductUuidsQuery);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

        Assert::implementsInterface($this->pqb, LegacyProductQueryBuilderInterface::class);
        $this->applyPermissionsOnPQB($this->pqb, $getProductUuidsQuery->userId());
        Assert::implementsInterface($this->pqb, LegacyProductQueryBuilderInterface::class);
        $this->applyProductSearchQueryParametersToPQB->apply(
            $this->pqb,
            $getProductUuidsQuery->searchFilters(),
            null,
            null,
            null
        );
        $this->productUuidQueryFetcher->initialize($this->pqb->buildQuery());

        return ProductUuidCursor::createFromFetcher($this->productUuidQueryFetcher);
    }

    private function applyPermissionsOnPQB(LegacyProductQueryBuilderInterface $pqb, int $userId): void
    {
        try {
            $isEnabled = $this->featureFlags->isEnabled('permission');
        } catch (\InvalidArgumentException) {
            $isEnabled = false;
        }

        if (!$isEnabled) {
            return;
        }

        Assert::notNull($this->getGrantedCategoryCodes);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        Assert::notNull($user);
        /* @phpstan-ignore-next-line */
        $grantedCategories = $this->getGrantedCategoryCodes->forGroupIds($user->getGroupsIds());

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategories, ['type_checking' => false]);
    }
}
