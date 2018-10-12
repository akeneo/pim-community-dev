<?php

namespace Specification\Akeneo\Channel\Component\Updater;

use Akeneo\Channel\Component\Updater\LocaleUpdater;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;

class LocaleUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_locale()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                LocaleInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_locale(LocaleInterface $locale)
    {
        $locale->setCode('en_US')->shouldBeCalled();

        $this->update($locale, ['code' => 'en_US'], []);
    }
}
