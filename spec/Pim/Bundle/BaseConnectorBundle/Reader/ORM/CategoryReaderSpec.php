<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Prophecy\Argument;

class CategoryReaderSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($categoryRepository);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_returns_a_category($categoryRepository, CategoryInterface $category, StepExecution $stepExecution)
    {
        $categoryRepository->getOrderedAndSortedByTreeCategories()->willReturn([$category]);
        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($category);
        $this->read()->shouldReturn(null);
    }
}
