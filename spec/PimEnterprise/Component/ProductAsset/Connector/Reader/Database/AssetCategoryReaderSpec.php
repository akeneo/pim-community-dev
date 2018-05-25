<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Reader\Database;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\CategoryInterface;

class AssetCategoryReaderSpec extends ObjectBehavior
{
    function let(CategoryRepositoryInterface $assetCategoryRepository)
    {
        $this->beConstructedWith($assetCategoryRepository);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface');
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
