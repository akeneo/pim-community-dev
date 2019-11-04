<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Query;

use Akeneo\Apps\back\tests\Integration\Fixtures\AppLoader;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppAndCredentialsByCodeQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppAndCredentialsByCodeQueryIntegration extends TestCase
{
    /** @var AppLoader */
    private $appLoader;

    /** @var SelectAppAndCredentialsByCodeQuery */
    private $selectAppAndCredentialsByCodeQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appLoader = $this->get('akeneo_app.fixtures.app_loader');
        $this->selectAppAndCredentialsByCodeQuery = $this->get('akeneo_app.persistence.query.select_app_and_credentials_by_code');
    }

    public function test_it_selects_apps()
    {
        $this->appLoader->createApp('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $app = $this->selectAppAndCredentialsByCodeQuery->execute('magento');
        Assert::assertSame('magento', $app->code());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
