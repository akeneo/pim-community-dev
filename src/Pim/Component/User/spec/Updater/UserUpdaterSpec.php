<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\User\Updater\UserUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UserUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_the_user_properties()
    {
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_user()
    {
    }
}
