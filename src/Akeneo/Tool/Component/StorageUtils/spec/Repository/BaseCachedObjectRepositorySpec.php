<?php

namespace spec\Akeneo\Component\StorageUtils\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class BaseCachedObjectRepositorySpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_cache_clearer()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface');
    }

    function it_is_an_identifiable_object_repository()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface');
    }

    function it_cached_objects($repository)
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $repository->findOneByIdentifier('objectidentifier1')
            ->willReturn($object1)
            ->shouldBeCalledTimes(1);

        $repository->findOneByIdentifier('objectidentifier2')
            ->willReturn($object2)
            ->shouldBeCalledTimes(1);

        $this->findOneByIdentifier('objectidentifier1')->shouldReturn($object1);
        $this->findOneByIdentifier('objectidentifier1')->shouldReturn($object1);
        $this->findOneByIdentifier('objectidentifier1')->shouldReturn($object1);
        $this->findOneByIdentifier('objectidentifier2')->shouldReturn($object2);
    }

    function it_clears_internal_cache($repository)
    {
        $object1 = new \stdClass();

        $repository->findOneByIdentifier('objectidentifier1')
            ->willReturn($object1)
            ->shouldBeCalledTimes(2);

        $this->findOneByIdentifier('objectidentifier1')->shouldReturn($object1);
        $this->clear();
        $this->findOneByIdentifier('objectidentifier1')->shouldReturn($object1);
    }

    function it_returns_null_on_non_existing_object($repository)
    {
        $repository->findOneByIdentifier('objectidentifier1')
            ->willReturn(null);

        $this->findOneByIdentifier('objectidentifier1')->shouldReturn(null);
    }
}
