<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch\ProductQueryBuilderAdapter;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ProductQueryBuilderAdapterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        ProductQueryBuilderOptionsResolverInterface $optionResolver,
        FeatureFlags $featureFlags,
        UserRepositoryInterface $userRepository
    ) {
        $optionResolver->resolve(['locale' => null, 'scope'  => null])->willReturn(['locale' => null, 'scope'  => null]);
        $featureFlags->isEnabled('permission')->willReturn(false);

        $this->beConstructedWith(
            $attributeRepository,
            $filterRegistry,
            $sorterRegistry,
            $optionResolver,
            $featureFlags,
            $userRepository,
            null
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductQueryBuilderAdapter::class);
        $this->shouldImplement(ProductQueryBuilderInterface::class);
    }

    function it_builds_the_query(
        FilterRegistryInterface $filterRegistry,
        FieldFilterInterface $fieldFilter
    ) {
        $filterRegistry->getFieldFilter('entity_type', Operators::EQUALS)
            ->shouldBeCalledOnce()->willReturn($fieldFilter);

        $this->buildQuery(null)->shouldReturn(['_source' => ['id', 'identifier', 'document_type']]);
    }

    function it_builds_the_query_with_a_user(
        FilterRegistryInterface $filterRegistry,
        FieldFilterInterface $fieldFilter
    ) {
        $filterRegistry->getFieldFilter('entity_type', Operators::EQUALS)
            ->shouldBeCalledOnce()->willReturn($fieldFilter);

        $this->buildQuery(1)->shouldReturn(['_source' => ['id', 'identifier', 'document_type']]);
    }

    function it_builds_the_query_with_a_search_after(
        FilterRegistryInterface $filterRegistry,
        FieldFilterInterface $fieldFilter
    ) {
        $filterRegistry->getFieldFilter('entity_type', Operators::EQUALS)
            ->shouldBeCalledOnce()->willReturn($fieldFilter);

        $uuid = Uuid::uuid4();
        $this->buildQuery(null, $uuid)
            ->shouldReturn(['_source' => ['id', 'identifier', 'document_type'], 'search_after' => ['product_' . $uuid->toString()]]);
    }

    function it_adds_permission_filters_and_builds_the_query(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        ProductQueryBuilderOptionsResolverInterface $optionResolver,
        FeatureFlags $featureFlags,
        UserRepositoryInterface $userRepository,
        $getGrantedCategoryCodes,
        FieldFilterInterface $fieldFilter1,
        FieldFilterInterface $fieldFilter2,
        UserInterface $user
    ) {
        FeatureHelper::skipSpecTestWhenPermissionFeatureIsNotActivated();

        $getGrantedCategoryCodes->beADoubleOf(GetGrantedCategoryCodes::class);

        $this->beConstructedWith(
            $attributeRepository,
            $filterRegistry,
            $sorterRegistry,
            $optionResolver,
            $featureFlags,
            $userRepository,
            $getGrantedCategoryCodes
        );

        $optionResolver->resolve(['locale' => null, 'scope'  => null])->willReturn(['locale' => null, 'scope'  => null]);
        $featureFlags->isEnabled('permission')->willReturn(true);

        $userRepository->findOneBy(['id' => 1])->willReturn($user);
        $user->getGroupsIds()->willReturn([100, 200, 300]);

        $getGrantedCategoryCodes->forGroupIds([100, 200, 300])->willReturn(['print', 'suppliers']);

        $filterRegistry->getFieldFilter('entity_type', Operators::EQUALS)
            ->shouldBeCalledOnce()->willReturn($fieldFilter1);
        $filterRegistry->getFieldFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED)
            ->shouldBeCalledOnce()->willReturn($fieldFilter2);

        $this->buildQuery(1)->shouldReturn(['_source' => ['id', 'identifier', 'document_type']]);
    }
}
