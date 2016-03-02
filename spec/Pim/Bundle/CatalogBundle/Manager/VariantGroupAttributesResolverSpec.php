<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class VariantGroupAttributesResolverSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\VariantGroupAttributesResolver');
    }

    function it_returns_non_eligible_attributes(
        $attributeRepository,
        GroupInterface $group,
        ProductTemplateInterface $template,
        AttributeInterface $length,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $identifier,
        Collection $collection
    ) {
        $group->getProductTemplate()->willReturn($template);
        $group->getAxisAttributes()->willReturn($collection);
        $collection->toArray()->willReturn([$length]);

        $template->getValuesData()->willReturn(['name' => 'foo', 'color' => 'bar']);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $attributeRepository->findBy(['unique' => true])->willReturn([$name, $identifier]);

        $attributes = [$length, $name, $color, $identifier];
        $this->getNonEligibleAttributes($group)->shouldReturn($attributes);
    }
}
