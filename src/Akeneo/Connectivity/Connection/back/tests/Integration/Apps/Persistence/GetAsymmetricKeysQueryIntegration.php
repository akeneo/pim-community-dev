<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAsymmetricKeysQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\PimConfigurationLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAsymmetricKeysQueryIntegration extends TestCase
{
    private GetAsymmetricKeysQuery $query;
    private PimConfigurationLoader $pimConfigurationLoader;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(GetAsymmetricKeysQuery::class);
        $this->pimConfigurationLoader = $this->get(PimConfigurationLoader::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_throws_an_exception_when_there_is_no_asymmetric_keys_into_the_database(): void
    {
        $this->resetPimConfiguration();
        $this->expectException(OpenIdKeysNotFoundException::class);
        $this->expectExceptionMessage(OpenIdKeysNotFoundException::MESSAGE);

        $this->query->execute();
    }

    public function test_it_gets_asymmetric_keys_from_the_database(): void
    {
        $this->pimConfigurationLoader->addPimconfiguration(
            SaveAsymmetricKeysQuery::OPTION_CODE,
            [AsymmetricKeys::PRIVATE_KEY => 'the_private_key', AsymmetricKeys::PUBLIC_KEY => 'the_public_key']
        );

        $result = $this->query->execute();

        $this->assertInstanceOf(AsymmetricKeys::class, $result);
        $this->assertEquals(
            [AsymmetricKeys::PRIVATE_KEY => 'the_private_key', AsymmetricKeys::PUBLIC_KEY => 'the_public_key'],
            $result->normalize()
        );
    }

    private function resetPimConfiguration(): void
    {
        $this->connection->executeQuery('DELETE FROM pim_configuration');
    }
}
