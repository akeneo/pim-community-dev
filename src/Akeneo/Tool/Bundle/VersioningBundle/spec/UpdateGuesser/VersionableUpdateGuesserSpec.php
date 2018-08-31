<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\VersionableUpdateGuesser;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;

class VersionableUpdateGuesserSpec extends ObjectBehavior
{
    function let(EntityManager $em)
    {
        $this->beConstructedWith(['stdClass']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VersionableUpdateGuesser::class);
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_guesses_versionable_entity_updates(
        AttributeInterface $attribute,
        $em
    ) {
        $object = new \stdClass();
        $this->guessUpdates($em, $attribute, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$attribute]);

        $this->guessUpdates($em, $object, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$object]);
    }

    function it_returns_no_pending_updates_if_not_given_versionable_class(
        $em,
        LocaleInterface $locale
    ) {
        $this->guessUpdates($em, $locale, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}
