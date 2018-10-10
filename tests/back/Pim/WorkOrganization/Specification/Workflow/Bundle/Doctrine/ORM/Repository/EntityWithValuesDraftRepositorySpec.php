<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository\EntityWithValuesDraftRepository;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;

class EntityWithValuesDraftRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = EntityWithValuesDraftRepository::class;
        $this->beConstructedWith($em, $class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EntityWithValuesDraftRepository::class);
    }

    function it_is_a_entity_with_values_draft_repository()
    {
        $this->shouldImplement(EntityWithValuesDraftRepositoryInterface::class);
    }
}
