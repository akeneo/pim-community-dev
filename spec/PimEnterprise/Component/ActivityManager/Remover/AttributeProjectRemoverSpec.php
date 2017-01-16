<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\AttributeProjectRemover;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverInterface;

class AttributeProjectRemoverSpec extends ObjectBehavior
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
        $this->shouldHaveType(AttributeProjectRemover::class);
        $this->shouldImplement(ProjectRemoverInterface::class);
    }

    function it_removes_projects_impacted_by_an_attribute_used_in_product_filters(
        $projectRepository,
        $projectRemover,
        $detacher,
        AttributeInterface $attribute,
        ProjectInterface $firstProject,
        ProjectInterface $secondProject
    ) {
        $attribute->getCode()->willReturn('release_date');

        $projectRepository->findAll()->willReturn([$firstProject, $secondProject]);

        $firstProject->getProductFilters()->willReturn([['field' => 'release_date']]);
        $secondProject->getProductFilters()->willReturn([['field' => 'family']]);

        $projectRemover->remove($firstProject)->shouldBeCalled();
        $projectRemover->remove($secondProject)->shouldNotBeCalled();

        $detacher->detach($firstProject)->shouldNotBeCalled();
        $detacher->detach($secondProject)->shouldBeCalled();

        $this->removeProjectsImpactedBy($attribute);
    }

    function it_removes_projects_impacted_only_by_an_attribute($projectRepository, ChannelInterface $channel)
    {
        $projectRepository->findAll()->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($channel);
    }
}
