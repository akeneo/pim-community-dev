<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocaleInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

class InMemoryFindRequiredValueKeyCollectionForChannelAndLocale implements FindRequiredValueKeyCollectionForChannelAndLocaleInterface
{
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): ValueKeyCollection {
        throw new NotImplementedException('__invoke');
    }
}
