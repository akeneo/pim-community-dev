<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Webmozart\Assert\Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_5_0_20200427144538_create_connection_error_index_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200427144538_create_connection_error_index';

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
        $client = $this->get('akeneo_connectivity.client.connection_error');
        $client->deleteIndex();
        Assert::false($client->hasIndex());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::true($client->hasIndex());
    }
}
