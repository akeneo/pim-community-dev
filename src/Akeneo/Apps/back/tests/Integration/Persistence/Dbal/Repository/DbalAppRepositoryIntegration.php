<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Dbal\Repository;

use Akeneo\Apps\back\tests\Integration\Fixtures\AppLoader;
use Akeneo\Apps\Domain\Model\ValueObject\AppImage;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
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

    public function test_it_finds_one_app_by_code()
    {
        $this->appLoader->createApp('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $app = $this->repository->findOneByCode('magento');

        Assert::assertInstanceOf(App::class, $app);
        Assert::assertSame('magento', (string) $app->code());
        Assert::assertSame('Magento Connector', (string) $app->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, (string) $app->flowType());
        Assert::assertNull($app->image());
        Assert::assertIsInt($app->clientId()->id());
        Assert::assertGreaterThan(0, $app->clientId()->id());
        Assert::assertIsInt($app->userId()->id());
        Assert::assertGreaterThan(0, $app->userId()->id());
    }

    public function test_it_updates_an_app_from_its_code()
    {
        $this->appLoader->createApp('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $app = $this->repository->findOneByCode('magento');
        $app->setLabel(new AppLabel('Pimgento'));
        $app->setFlowType(new FlowType(FlowType::OTHER));
        $app->setImage(new AppImage('a/b/c/app_image.jpg'));

        $this->repository->update($app);

        $result = $this->selectAppFromDb('magento');

        Assert::assertSame('magento', $result['code']);
        Assert::assertSame('Pimgento', $result['label']);
        Assert::assertSame(FlowType::OTHER, $result['flow_type']);
        Assert::assertSame('a/b/c/app_image.jpg' , $result['image']);
        Assert::assertNotNull($result['client_id']);
        Assert::assertNotNull($result['user_id']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function selectAppFromDb(string $code): array
    {
        $query = <<<SQL
    SELECT code, label, flow_type, client_id, user_id, image
    FROM akeneo_app
    WHERE code = :code
SQL;
        $statement = $this->dbal->executeQuery($query, ['code' => $code]);

        return $statement->fetch();
    }
}
