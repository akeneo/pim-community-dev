<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\AbstractEntityWithValuesQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * This class is an adapter between the former implementation of PQB and the new one.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductQueryBuilderAdapter extends AbstractEntityWithValuesQueryBuilder implements ProductQueryBuilderInterface
{
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        ProductQueryBuilderOptionsResolverInterface $optionResolver,
        private FeatureFlags $featureFlags,
        private UserRepositoryInterface $userRepository,
        private ?GetGrantedCategoryCodes $getGrantedCategoryCodes
    ) {
        $cursorFactory = new class implements CursorFactoryInterface {
            /**
             * @param mixed $queryBuilder
             * @param array<string, mixed> $options
             * @return CursorInterface
             */
            public function createCursor($queryBuilder, array $options = []): CursorInterface
            {
                throw new \RuntimeException('This class should not be called anymore');
            }
        };

        parent::__construct(
            $attributeRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $optionResolver,
            [
                'locale' => null,
                'scope'  => null,
            ]
        );
        $this->setQueryBuilder(new SearchQueryBuilder());
    }

    public function buildQuery(?int $userId, ?UuidInterface $searchAfterUuid = null): array
    {
        if (null !== $userId) {
            $this->applyPermissions($userId);
        }
        $this->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        $query = $this->getQueryBuilder()->getQuery();
        if ($searchAfterUuid) {
            $query['search_after'] = ['product_' . $searchAfterUuid->toString()];
        }

        return $query;
    }

    private function applyPermissions(int $userId): void
    {
        try {
            if (!$this->featureFlags->isEnabled('permission')) {
                return;
            }
        } catch (\InvalidArgumentException) {
            return;
        }

        Assert::notNull($this->getGrantedCategoryCodes);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        Assert::notNull($user);
        /* @phpstan-ignore-next-line */
        $grantedCategories = $this->getGrantedCategoryCodes->forGroupIds($user->getGroupsIds());

        $this->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategories, ['type_checking' => false]);
    }
}
