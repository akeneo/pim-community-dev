<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAsymmetricKeysQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateAsymmetricKeysHandlerIntegration extends TestCase
{
    private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler;
    private GetAsymmetricKeysQuery $getAsymmetricKeysQuery;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generateAsymmetricKeysHandler = $this->get(GenerateAsymmetricKeysHandler::class);
        $this->getAsymmetricKeysQuery = $this->get(GetAsymmetricKeysQuery::class);
        $this->connection = $this->get('database_connection');
    }

    public function test_it_save_new_asymmetric_keys(): void
    {
        $this->resetPimConfiguration();

        $this->expectException(OpenIdKeysNotFoundException::class);
        $this->expectExceptionMessage('No OpenId keys');
        $this->getAsymmetricKeysQuery->execute();

        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());

        $result = $this->getAsymmetricKeysQuery->execute();

        Assert::assertInstanceOf(AsymmetricKeys::class, $result);
    }

    private function resetPimConfiguration(): void
    {
        $this->connection->executeQuery('DELETE FROM pim_configuration');
    }
}
