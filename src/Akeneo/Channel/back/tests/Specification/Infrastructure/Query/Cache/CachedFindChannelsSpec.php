<?php

namespace Specification\Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\ConversionUnitCollection;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use PhpSpec\ObjectBehavior;

class CachedFindChannelsSpec extends ObjectBehavior
{
    public function let(FindChannels $findChannels)
    {
        $this->beConstructedWith($findChannels);
    }

    public function it_finds_all_channels_and_caches_them(
        FindChannels $findChannels
    ) {
        $findChannels
            ->findAll()
            ->willReturn([
                new Channel(
                    'ecommerce',
                    ['en_US', 'fr_FR'],
                    LabelCollection::fromArray([
                        'en_US' => 'Ecommerce',
                    ]),
                    ['USD'],
                    ConversionUnitCollection::fromArray([
                        'an_measurement_attribute' => 'GRAM',
                        'another_measurement_attribute' => 'POUND'
                    ]),
                ),
                new Channel(
                    'mobile',
                    ['en_US'],
                    LabelCollection::fromArray([
                        'en_US' => 'Mobile',
                    ]),
                    ['EUR'],
                    ConversionUnitCollection::fromArray([
                        'an_measurement_attribute' => 'GRAM',
                        'another_measurement_attribute' => 'POUND'
                    ]),
                ),
            ])
            ->shouldBeCalledOnce();

        $this->findAll();
        $this->findAll();
        $this->findAll();
    }
}
