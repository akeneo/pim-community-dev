<?php

namespace Akeneo\AssetManager\Common\Fake\Channel;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;

class InMemoryFindChannels implements FindChannels
{
    public function findAll(): array
    {
        $mobileChannel = new Channel('mobile', ['de_DE'], LabelCollection::fromArray([]), []);
        $printChannel = new Channel('print', ['en_US'], LabelCollection::fromArray([]), []);
        $ecommerceChannel = new Channel('ecommerce', ['en_US', 'fr_FR'], LabelCollection::fromArray([]), []);

        return [
            $mobileChannel,
            $printChannel,
            $ecommerceChannel,
        ];
    }
}
