<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\CurrencyInterface;

class CurrencyUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\CurrencyUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Akeneo\Channel\Component\Model\CurrencyInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_currency(CurrencyInterface $currency)
    {
        $currency->setCode('USD')->shouldBeCalled();
        $currency->setActivated(true)->shouldBeCalled();

        $this->update($currency, [
            'code'    => 'USD',
            'enabled' => true
        ], []);
    }
}
