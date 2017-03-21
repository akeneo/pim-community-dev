<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;

class AttributeOptionUpdateGuesserSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $optionValue
    ) {
        $option->getAttribute()->willReturn($attribute);
        $optionValue->getOption()->willReturn($option);
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
}
