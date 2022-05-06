<?php

namespace Akeneo\ReferenceEntity\Common\Fake\Channel;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;

class InMemoryFindChannels implements FindChannels
{
    /** @var array<string, Channel> */
    private $channels = [];

    public function __construct()
    {
        $this->channels = [
            new Channel('mobile', ['de_DE'], LabelCollection::fromArray([]), []),
            new Channel('print', ['en_US'], LabelCollection::fromArray([]), []),
            new Channel('ecommerce', ['en_US', 'fr_FR'], LabelCollection::fromArray([]), []),
        ];
    }

    public function findAll(): array
    {
        return $this->channels;
    }

    public function setChannels(array $channels): void
    {
        $this->channels = $channels;
    }
}
