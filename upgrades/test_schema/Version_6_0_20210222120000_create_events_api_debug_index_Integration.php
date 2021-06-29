<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20210222120000_create_events_api_debug_index_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210222120000_create_events_api_debug_index';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_creates_the_new_index()
    {
        /** @var Client $client */
        $client = $this->get('akeneo_connectivity.client.events_api_debug');

        $client->deleteIndex();
        Assert::false($client->hasIndex());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::true($client->hasIndex());
    }
}
