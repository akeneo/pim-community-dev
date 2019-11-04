<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Query;

use Akeneo\Apps\back\tests\Integration\Fixtures\AppLoader;
use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppsQueryIntegration extends TestCase
{
    /** @var AppLoader */
    private $appLoader;

    /** @var SelectAppsQuery */
    private $selectAppsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appLoader = $this->get('akeneo_app.fixtures.app_loader');
        $this->selectAppsQuery = $this->get('akeneo_app.persistence.query.select_apps');
    }

    public function test_it_fetches_apps()
    {
        $this->appLoader->createApp('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        sleep(1);
        $this->appLoader->createApp('bynder', 'Bynder', FlowType::OTHER);

        $apps = $this->selectAppsQuery->execute();

        Assert::assertCount(2, $apps);
        Assert::assertContainsOnlyInstancesOf(App::class, $apps);
        Assert::assertSame('magento', $apps[0]->code());
        Assert::assertSame('bynder', $apps[1]->code());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
