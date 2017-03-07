<?php

namespace spec\PimEnterprise\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\AssetAnalyticProvider;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\AttributeGroupAccessAnalyticProvider;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\CategoryAccessAnalyticProvider;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\LocaleAccessAnalyticProvider;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\ProductDraftRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\ProjectRepository;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

class DBDataCollectorSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepository $draftRepository,
        ProjectRepository $projectRepository,
        AssetAnalyticProvider $assetRepository,
        PublishedProductRepositoryInterface $publishedRepository,
        LocaleAccessAnalyticProvider $localeAccessRepository,
        CategoryAccessAnalyticProvider $categoryAccessRepository,
        AttributeGroupAccessAnalyticProvider $attributeGroupAccessRepository
    ) {
        $this->beConstructedWith(
            $draftRepository,
            $projectRepository,
            $assetRepository,
            $publishedRepository,
            $localeAccessRepository,
            $categoryAccessRepository,
            $attributeGroupAccessRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\AnalyticsBundle\DataCollector\DBDataCollector');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects_database_statistics(
        $draftRepository,
        $projectRepository,
        $assetRepository,
        $publishedRepository,
        $localeAccessRepository,
        $categoryAccessRepository,
        $attributeGroupAccessRepository
    ) {
        $draftRepository->countAll()->willReturn(10);
        $projectRepository->countAll()->willReturn(20);
        $assetRepository->countAll()->willReturn(30);
        $publishedRepository->countAll()->willReturn(40);
        $localeAccessRepository->countCustomAccesses()->willReturn(50);
        $categoryAccessRepository->countCustomAccesses()->willReturn(60);
        $attributeGroupAccessRepository->countCustomAccesses()->willReturn(70);

        $this->collect()->shouldReturn(
            [
                'nb_product_drafts'                  => 10,
                'nb_projects'                        => 20,
                'nb_assets'                          => 30,
                'nb_published_products'              => 40,
                'nb_custom_locale_accesses'          => 50,
                'nb_custom_category_accesses'        => 60,
                'nb_custom_attribute_group_accesses' => 70,
            ]
        );
    }
}
