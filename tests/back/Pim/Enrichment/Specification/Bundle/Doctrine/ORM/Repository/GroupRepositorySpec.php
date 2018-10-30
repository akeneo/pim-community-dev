<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

class GroupRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'group';

        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_group_repository()
    {
        $this->shouldImplement(GroupRepositoryInterface::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupRepository::class);
    }
}
