<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Cache\Locale;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Cache\Locale\CacheFindActivatedLocalesByIdentifiers;
use PhpSpec\ObjectBehavior;

class CacheFindActivatedLocalesByIdentifiersSpec extends ObjectBehavior
{
    function let(FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers)
    {
        $this->beConstructedWith($findActivatedLocalesByIdentifiers);
    }

    function it_is_a_query_to_find_activated_locales_by_identifiers()
    {
        $this->shouldImplement(FindActivatedLocalesByIdentifiersInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CacheFindActivatedLocalesByIdentifiers::class);
    }

    function it_keeps_in_cache_the_activated_locales_by_identifiers_found(
        FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers
    ) {
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE']);
        $expectedActivatedLocales = LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']);

        $findActivatedLocalesByIdentifiers->find($localeIdentifiers)
            ->shouldBeCalledOnce()
            ->willReturn($expectedActivatedLocales);

        $this->find($localeIdentifiers)->shouldBeLike($expectedActivatedLocales);
        $this->find($localeIdentifiers)->shouldBeLike($expectedActivatedLocales);

        $this->find(LocaleIdentifierCollection::fromNormalized(['en_US', 'de_DE']))
            ->shouldBeLike(LocaleIdentifierCollection::fromNormalized(['en_US']));
    }

    function it_loads_only_the_locales_that_are_not_in_cache(
        FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers
    ) {
        $findActivatedLocalesByIdentifiers
            ->find(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE']))
            ->shouldBeCalledOnce()
            ->willReturn(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']));

        $findActivatedLocalesByIdentifiers
            ->find(LocaleIdentifierCollection::fromNormalized(['en_AU', 'fr_BE']))
            ->shouldBeCalledOnce()
            ->willReturn(LocaleIdentifierCollection::fromNormalized(['en_AU']));

        $this->find(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE']))
            ->shouldBeLike(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']));

        $this->find(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'en_AU', 'fr_BE']))
            ->shouldBeLike(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'en_AU']));
    }
}
