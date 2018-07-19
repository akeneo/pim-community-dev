<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository\PublishedAssociationRepository;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedAssociationRepositoryInterface;

class PublishedAssociationRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = PublishedAssociationRepository::class;
        $this->beConstructedWith($em, $class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PublishedAssociationRepository::class);
    }

    function it_is_a_published_association_repository()
    {
        $this
            ->shouldImplement(PublishedAssociationRepositoryInterface::class);
    }
}
