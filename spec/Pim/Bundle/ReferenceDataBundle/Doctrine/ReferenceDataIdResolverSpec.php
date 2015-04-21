<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

class ReferenceDataIdResolverSpec extends ObjectBehavior
{
    public function let(ReferenceDataRepositoryResolverInterface $resolver)
    {
        $this->beConstructedWith($resolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver');
    }

    function it_resolves_an_id($resolver, ObjectRepository $repository, ReferenceDataInterface $referenceData)
    {
        $resolver->resolve('fabrics')->willReturn($repository);
        $repository->findOneBy(['code' => 'leather'])->willReturn($referenceData);
        $referenceData->getId()->willReturn(11);

        $this->resolve('fabrics', 'leather')->shouldReturn(11);
    }

    function it_resolves_several_ids($resolver, ObjectRepository $repository, ReferenceDataInterface $referenceData1, ReferenceDataInterface $referenceData2)
    {
        $resolver->resolve('fabrics')->willReturn($repository);
        $repository->findOneBy(['code' => 'leather'])->willReturn($referenceData1);
        $repository->findOneBy(['code' => 'wool'])->willReturn($referenceData2);
        $referenceData1->getId()->willReturn(11);
        $referenceData2->getId()->willReturn(42);

        $this->resolve('fabrics', ['leather', 'wool'])->shouldReturn([11, 42]);
    }
}
