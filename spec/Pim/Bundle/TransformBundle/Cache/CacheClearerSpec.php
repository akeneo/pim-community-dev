<?php

namespace spec\Pim\Bundle\TransformBundle\Cache;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CacheClearerSpec extends ObjectBehavior
{
    function let(
        DoctrineCache $doctrineCache,
        RegistryInterface $managerRegistry,
        EntityManager $entityManager,
        UnitOfWork $uow
    ) {
        $this->beConstructedWith($doctrineCache, $managerRegistry);
        $managerRegistry->getManagers()->willReturn([$entityManager]);
        $entityManager->getUnitOfWork()->willReturn($uow);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Cache\CacheClearer');
    }

    function it_clears_the_import_cache(
        DoctrineCache $doctrineCache,
        EntityManager $entityManager,
        UnitOfWork $uow
    ) {
        $this->setNonClearableEntities(['NonClearable']);
        $uow->getIdentityMap()->willReturn(
            [
                'NonClearable' => [],
                'Clearable' => []
            ]
        );
        $entityManager->clear('Clearable')->shouldBeCalled();
        $doctrineCache->clear(['NonClearable'])->shouldBeCalled();

        $this->clear();
    }
}
