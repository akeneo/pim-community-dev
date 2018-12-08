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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Locale;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Locale\CacheFindActivatedLocalesByIdentifiers;
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

    function it_keeps_in_cache_the_activated_locales_by_identifiers_found($findActivatedLocalesByIdentifiers)
    {
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE']);
        $expectedActivatedLocales = LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']);

        $findActivatedLocalesByIdentifiers->__invoke($localeIdentifiers)
            ->shouldBeCalledOnce()
            ->willReturn($expectedActivatedLocales);

        $this->__invoke($localeIdentifiers)->shouldBeLike($expectedActivatedLocales);
        $this->__invoke($localeIdentifiers)->shouldBeLike($expectedActivatedLocales);

        $this->__invoke(LocaleIdentifierCollection::fromNormalized(['en_US', 'de_DE']))
            ->shouldBeLike(LocaleIdentifierCollection::fromNormalized(['en_US']));
    }

    function it_loads_only_the_locales_that_are_not_in_cache($findActivatedLocalesByIdentifiers)
    {
        $findActivatedLocalesByIdentifiers
            ->__invoke(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE']))
            ->shouldBeCalledOnce()
            ->willReturn(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']));

        $findActivatedLocalesByIdentifiers
            ->__invoke(LocaleIdentifierCollection::fromNormalized(['en_AU', 'fr_BE']))
            ->shouldBeCalledOnce()
            ->willReturn(LocaleIdentifierCollection::fromNormalized(['en_AU']));

        $this->__invoke(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE']))
            ->shouldBeLike(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']));

        $this->__invoke(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'en_AU', 'fr_BE']))
            ->shouldBeLike(LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'en_AU']));
    }
}
