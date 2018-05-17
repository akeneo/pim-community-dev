<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Remover;

use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Remover\CategoryProjectRemover;
use PimEnterprise\Component\TeamworkAssistant\Remover\ProjectRemoverInterface;

class CategoryProjectRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectRepository $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith($projectRepository, $projectRemover, $detacher);
    }

    function it_is_a_project_remover()
    {
        $this->shouldHaveType(CategoryProjectRemover::class);
        $this->shouldImplement(ProjectRemoverInterface::class);
    }

    function it_removes_projects_impacted_by_a_category_used_in_product_filters(
        $projectRepository,
        $projectRemover,
        $detacher,
        CategoryInterface $category,
        ProjectInterface $firstProject,
        ProjectInterface $secondProject
    ) {
        $category->getCode()->willReturn('clothing');

        $projectRepository->findAll()->willReturn([$firstProject, $secondProject]);

        $firstProject->getProductFilters()->willReturn([['field' => 'categories', 'value' => ['clothing']]]);
        $secondProject->getProductFilters()->willReturn([['field' => 'categories', 'value' => ['camera']]]);

        $projectRemover->remove($firstProject)->shouldBeCalled();
        $projectRemover->remove($secondProject)->shouldNotBeCalled();

        $detacher->detach($firstProject)->shouldNotBeCalled();
        $detacher->detach($secondProject)->shouldBeCalled();

        $this->removeProjectsImpactedBy($category);
    }

    function it_removes_projects_impacted_only_by_a_category_removal(
        ChannelInterface $channel,
        CategoryInterface $category
    ) {
        $this->isSupported($channel, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($category, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($category, StorageEvents::PRE_REMOVE)->shouldReturn(true);
    }
}
