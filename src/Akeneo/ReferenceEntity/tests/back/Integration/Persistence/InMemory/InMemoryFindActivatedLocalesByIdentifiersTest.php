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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use PHPUnit\Framework\TestCase;

class InMemoryFindActivatedLocalesByIdentifiersTest extends TestCase
{
    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $findActivatedLocalesByIdentifiers;

    public function setUp()
    {
        $this->findActivatedLocalesByIdentifiers = new InMemoryFindActivatedLocalesByIdentifiers();
    }

    /**
     * @test
     */
    public function it_returns_a_collection_of_activated_locales_for_the_given_locale_identifiers()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));

        $expectedActivatedLocales = LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR']);
        $activatedLocales = ($this->findActivatedLocalesByIdentifiers)(
            LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'en_UK'])
        );

        $this->assertEquals($expectedActivatedLocales, $activatedLocales);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_collection_if_no_activated_locales_are_found()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));

        $activatedLocales = ($this->findActivatedLocalesByIdentifiers)(
            LocaleIdentifierCollection::fromNormalized(['de_DE', 'en_UK'])
        );

        $this->assertInstanceOf(LocaleIdentifierCollection::class, $activatedLocales);
        $this->assertTrue($activatedLocales->isEmpty());
    }
}
