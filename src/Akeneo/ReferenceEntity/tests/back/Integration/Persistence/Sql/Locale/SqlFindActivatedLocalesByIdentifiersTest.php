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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Locale;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindActivatedLocalesByIdentifiersTest extends SqlIntegrationTestCase
{
    private $localesAreActivated;

    public function setUp(): void
    {
        parent::setUp();

        $this->localesAreActivated = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_finds_the_activated_locales_from_a_list_of_locale_identifiers(): void
    {
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'fr_BE', 'en_US']);

        $localesFound = ($this->localesAreActivated)($localeIdentifiers);
        $localesFound = $localesFound->normalize();
        sort($localesFound);

        $this->assertSame(['en_US', 'fr_FR'], $localesFound);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_collection_if_no_activated_locales_have_been_found(): void
    {
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized(['ww_ZZ', 'fr_BE']);
        $localesFound = ($this->localesAreActivated)($localeIdentifiers);

        $this->assertTrue($localesFound->isEmpty());
    }
}
