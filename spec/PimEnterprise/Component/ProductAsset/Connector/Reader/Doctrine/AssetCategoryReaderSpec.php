<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

class AssetCategoryReaderSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $assetCategoryRepository
    ) {
        $this->beConstructedWith($assetCategoryRepository);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_returns_an_asset_category(
        $assetCategoryRepository,
        CategoryInterface $category,
        StepExecution $stepExecution
    ) {
        $assetCategoryRepository->getOrderedAndSortedByTreeCategories()->willReturn([$category]);
        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($category);
        $this->read()->shouldReturn(null);
    }
}
