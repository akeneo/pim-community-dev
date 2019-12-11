<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd\Audit;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\back\tests\EndToEnd\WebTestCase;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByAppEndToEnd extends WebTestCase
{
    public function test_it_finds_apps_event_by_created_product()
    {
        // load fixtures
        $appFranklin = $this->get('akeneo_app.fixtures.app_loader')->createApp('franklin', 'Franklin', FlowType::DATA_SOURCE);
        $appErp = $this->get('akeneo_app.fixtures.app_loader')->createApp('erp', 'ERP', FlowType::DATA_SOURCE);
        $this->loadAuditData([$appFranklin, $appErp]);
        $this->createAdminUser();

        $authParams = [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ];
        $this->client->request('GET', '/rest/apps/franklin');
        $response = $this->client->getResponse();
        var_dump($response->getContent());
        var_dump($response->getStatusCode());

        $this->client->request('GET', '/rest/apps/audit/source-apps-event', ['event_type' => 'product_created']);
        $response = $this->client->getResponse();

        var_dump($response->getContent());
        var_dump($response->getStatusCode());
    }

    private function loadAuditData(array $apps): void
    {
        // TODO: Calculate dates from "now +1day" to "now -2 days"
        $dates = ['2019-12-08', '2019-12-09', '2019-12-10', '2019-12-11'];
        foreach ($apps as $app) {
            $count = 0;
            foreach ($dates as $date) {
                foreach (['product_created', 'product_updated'] as $eventType) {
                    $this
                        ->get('akeneo_app.fixtures.audit_loader')
                        ->insertData($app->code(), $date, $count++, $eventType);
                }
            }
        }
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
