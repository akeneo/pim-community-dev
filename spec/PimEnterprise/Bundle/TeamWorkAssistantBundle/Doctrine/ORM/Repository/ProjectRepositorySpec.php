<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository\ProjectRepository;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamWorkAssistant\Model\Project;

class ProjectRepositorySpec extends ObjectBehavior
{
    function let(ClassMetadata $classMetadata)
    {
        $this->beConstructedWith(Project::class, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectRepository::class);
    }

    function it_is_an_object_identifiable_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_searchable_repository()
    {
        $this->shouldImplement(SearchableRepositoryInterface::class);
    }

    function it_is_a_cursorable_repository()
    {
        $this->shouldImplement(CursorableRepositoryInterface::class);
    }

    function its_identifier_is_id()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
