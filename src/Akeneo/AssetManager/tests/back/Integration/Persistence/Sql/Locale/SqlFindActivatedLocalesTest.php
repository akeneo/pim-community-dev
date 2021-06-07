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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Locale;

use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindActivatedLocalesTest extends SqlIntegrationTestCase
{
    private FindActivatedLocalesInterface $localesAreActivated;

    public function setUp(): void
    {
        parent::setUp();

        $this->localesAreActivated = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_activated_locales');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_finds_the_activated_locales(): void
    {
        $localesFound = $this->localesAreActivated->findAll();
        sort($localesFound);

        $this->assertSame(['de_DE', 'en_US', 'fr_FR'], $localesFound);
    }
}
