<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Apps\back\tests\Integration\Fixtures\AppLoader;
use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppWithCredentialsByCodeQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppWithCredentialsByCodeQueryIntegration extends TestCase
{
    /** @var AppLoader */
    private $appLoader;

    /** @var SelectAppWithCredentialsByCodeQuery */
    private $selectAppWithCredentialsByCodeQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appLoader = $this->get('akeneo_app.fixtures.app_loader');
        $this->selectAppWithCredentialsByCodeQuery = $this->get('akeneo_app.persistence.query.select_app_with_credentials_by_code');
    }

    public function test_it_finds_an_app_with_its_credentials()
    {
        $this->appLoader->createApp('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $app = $this->selectAppWithCredentialsByCodeQuery->execute('magento');

        Assert::assertInstanceOf(AppWithCredentials::class, $app);
        Assert::assertSame('magento', $app->code());
        Assert::assertSame('Magento Connector', $app->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, $app->flowType());
        Assert::assertNotNull($app->clientId());
        Assert::assertNotNull($app->secret());
        Assert::assertNotNull($app->username());
        Assert::assertNull($app->image());
    }

    public function test_it_does_not_find_an_app_from_its_code()
    {
        $app = $this->selectAppWithCredentialsByCodeQuery->execute('magento');

        Assert::assertNull($app);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
