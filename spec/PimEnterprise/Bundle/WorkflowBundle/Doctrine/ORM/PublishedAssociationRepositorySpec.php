<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

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

    function it_should_be_a_published_association_repository()
    {
        $this->shouldImplement('PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface');
    }
}
