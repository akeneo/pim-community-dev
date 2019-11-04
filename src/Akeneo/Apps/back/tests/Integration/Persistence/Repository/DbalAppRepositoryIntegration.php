<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Repository;

use Akeneo\Apps\back\tests\Integration\Fixtures\AppLoader;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Infrastructure\Persistence\Dbal\Repository\DbalAppRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalAppRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $dbal;

    /** @var DbalAppRepository */
    private $repository;

    /** @var AppLoader */
    private $appLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbal = $this->get('database_connection');
        $this->repository = $this->get('akeneo_app.persistence.repository.app');
        $this->appLoader = $this->get('akeneo_app.fixtures.app_loader');
    }

    public function test_it_saves_a_new_app()
    {
        $clientId = $this->appLoader->createClient('magento');
        $userId = $this->appLoader->createUser('magento', 'Magento connector');
        $app = new App('magento', 'Magento connector', FlowType::DATA_DESTINATION, $clientId, $userId);

        $this->repository->create($app);

        $query = <<<SQL
    SELECT code, label, flow_type, client_id, user_id
    FROM akeneo_app
    WHERE code = :code
SQL;
        $statement = $this->dbal->executeQuery($query, ['code' => 'magento']);
        $result = $statement->fetch();

        Assert::assertSame('magento', $result['code']);
        Assert::assertSame('Magento connector', $result['label']);
        Assert::assertSame(FlowType::DATA_DESTINATION, $result['flow_type']);
        Assert::assertNotNull($result['client_id']);
        Assert::assertIsInt((int) $result['client_id']);
        Assert::assertNotNull($result['user_id']);
        Assert::assertIsInt((int) $result['user_id']);
    }

    public function it_finds_one_app_by_code()
    {
        $this->appLoader->createApp('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $app = $this->repository->findOneByCode('magento');

        Assert::assertInstanceOf(App::class, $app);
        Assert::assertSame('magento', $app->code());
        Assert::assertSame('Magento connector', $app->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, $app->flowType());
        Assert::assertIsInt($app->clientId());
        Assert::assertGreaterThan(0, $app->clientId());
        Assert::assertIsInt($app->userId());
        Assert::assertGreaterThan(0, $app->userId());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
