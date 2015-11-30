<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Annotations\Annotation\Attribute;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GroupManagerSpec extends ObjectBehavior
{
    function let(
        GroupTypeRepositoryInterface $groupTypeRepository,
        AttributeRepositoryInterface $attRepository
    ) {
        $this->beConstructedWith($groupTypeRepository, $attRepository);
    }

    function it_provides_available_axis_as_a_sorted_choice(
        $attRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attribute1->getId()->willReturn(1);
        $attribute1->getLabel()->willReturn('Foo');

        $attribute2->getId()->willReturn(2);
        $attribute2->getLabel()->willReturn('Bar');

        $attRepository->findAllAxis()->willReturn([$attribute1, $attribute2]);

        $this->getAvailableAxisChoices()->shouldReturn([2 => 'Bar', 1 => 'Foo']);
    }
}
