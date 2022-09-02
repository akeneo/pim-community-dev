<?php

declare(strict_types=1);

namespace spec\AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Normalizer;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Permission\Bundle\Normalizer\Flat\CategoryNormalizer;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllAppsUserGroupLabelQuery;
use PhpSpec\ObjectBehavior;

class CategoryAccessNormalizerSpec extends ObjectBehavior
{
    public function let(
        CategoryNormalizer $categoryNormalizer,
        GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ): void
    {
        $this->beConstructedWith(
            $categoryNormalizer,
            $getAllAppsUserGroupLabelQuery,
        );
    }

    public function it_replaces_app_codes_by_app_labels(
        CategoryNormalizer $categoryNormalizer,
        GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ): void
    {
        $category = new Category();
        $getAllAppsUserGroupLabelQuery->execute()->willReturn([['code' => 'app_123456789', 'label' => 'App Label']]);
        $categoryNormalizer->normalize($category, 'flat', [])->willReturn([
            'view_permission' => 'foo,bar,app_123456789',
            'edit_permission' => 'foo,app_123456789',
            'own_permission' => 'bar',
        ]);

        $this->normalize($category, 'flat')->shouldReturn([
            'view_permission' => 'foo,bar,App Label',
            'edit_permission' => 'foo,App Label',
            'own_permission' => 'bar',
        ]);
    }

    public function it_returns_the_original_flat_category(
        CategoryNormalizer $categoryNormalizer,
        GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ): void
    {
        $category = new Category();
        $getAllAppsUserGroupLabelQuery->execute()->willReturn([]);
        $categoryNormalizer->normalize($category, 'flat', [])->willReturn([
            'view_permission' => 'foo,bar,app_123456789',
            'edit_permission' => 'foo,app_123456789',
            'own_permission' => 'bar',
        ]);

        $this->normalize($category, 'flat')->shouldReturn([
            'view_permission' => 'foo,bar,app_123456789',
            'edit_permission' => 'foo,app_123456789',
            'own_permission' => 'bar',
        ]);
    }
}
