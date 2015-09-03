<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
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
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
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
