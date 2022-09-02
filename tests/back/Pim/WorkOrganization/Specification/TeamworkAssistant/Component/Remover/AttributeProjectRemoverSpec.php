<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover;

use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover\AttributeProjectRemover;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover\ProjectRemoverInterface;

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

    function it_removes_projects_impacted_only_by_an_attribute_removal(
        ChannelInterface $channel,
        AttributeInterface $attribute
    ) {
        $this->isSupported($channel, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($attribute, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($attribute, StorageEvents::PRE_REMOVE)->shouldReturn(true);
    }
}
