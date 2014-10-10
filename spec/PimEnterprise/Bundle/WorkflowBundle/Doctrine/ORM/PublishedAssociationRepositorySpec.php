<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class PublishedAssociationRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = 'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductAssociation';
        $this->beConstructedWith($em, $class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\PublishedAssociationRepository');
    }

    function it_is_a_published_association_repository()
    {
        $this
            ->shouldImplement('PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface');
    }
}
