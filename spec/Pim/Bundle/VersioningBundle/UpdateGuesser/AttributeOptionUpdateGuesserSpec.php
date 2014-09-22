<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class AttributeOptionUpdateGuesserSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        ProductRepositoryInterface $repository,
        EntityManager $em,
        AbstractAttribute $attribute,
        AttributeOption $option,
        AttributeOptionValue $optionValue
    ) {
        $registry->getRepository('product')->willReturn($repository);
        $repository->findAllWithAttributeOption($option)->willReturn([]);

        $option->getAttribute()->willReturn($attribute);
        $optionValue->getOption()->willReturn($option);

        $this->beConstructedWith($registry, 'product');
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
    }

    function it_supports_entity_updates_and_deletion()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(false);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_marks_attributes_as_updated_when_an_attribute_option_is_removed_or_updated($em, $attribute, $option)
    {
        $this->guessUpdates($em, $option, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$attribute]);
        $this->guessUpdates($em, $option, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$attribute]);
    }

    function it_marks_attributes_as_updated_when_an_attribute_option_value_is_removed_or_updated(
        $em,
        $attribute,
        $optionValue
    ) {
        $this->guessUpdates($em, $optionValue, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$attribute]);
        $this->guessUpdates($em, $optionValue, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$attribute]);
    }

    function it_marks_products_as_updated_when_an_attribute_option_is_removed(
        $em,
        $repository,
        $attribute,
        $option,
        ProductInterface $foo,
        ProductInterface $bar
    ) {
        $repository->findAllWithAttributeOption($option)->willReturn([$foo, $bar]);

        $this->guessUpdates($em, $option, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$attribute]);
        $this->guessUpdates($em, $option, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$attribute, $foo, $bar]);
    }
}
