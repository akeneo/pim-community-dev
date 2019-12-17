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
use PHPUnit\Framework\Assert;
use Symfony\Component\BrowserKit\Cookie;
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
        $this->get('akeneo_app.fixtures.app_loader')->createApp('franklin', 'Franklin', FlowType::DATA_SOURCE);
        $this->get('akeneo_app.fixtures.app_loader')->createApp('erp', 'ERP', FlowType::DATA_SOURCE);
        $this->loadAuditData();
        $this->createAdminUser();
        $this->authenticateClient();

        $this->client->request('GET', '/rest/apps/audit/source-apps-event', ['event_type' => 'product_created']);
        $response = $this->client->getResponse();

        Assert::assertTrue($response->isOk());
        Assert::assertJsonStringNotEqualsJsonFile(
            realpath(__DIR__.'/../Resources/json_response/count_daily_events_by_app.json'),
            $response->getContent()
        );
    }

    private function loadAuditData(): void
    {
        $eventDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $auditLoader = $this->get('akeneo_app.fixtures.audit_loader');
        // today
        $auditLoader->insertData('franklin', $eventDate, 11, 'product_created');
        $auditLoader->insertData('erp', $eventDate, 28, 'product_updated');
        $auditLoader->insertData('erp', $eventDate, 37, 'product_created');
        // yesterday
        $auditLoader->insertData('franklin', $eventDate->modify('-1 day'), 5, 'product_created');
        $auditLoader->insertData('franklin', $eventDate, 132, 'product_updated');
        // 2 days ago
        $auditLoader->insertData('franklin', $eventDate->modify('-1 day'), 10, 'product_created');
        $auditLoader->insertData('franklin', $eventDate, 7, 'product_created');
        // 10 days ago
        $auditLoader->insertData('franklin', $eventDate->modify('-7 day'), 15, 'product_created');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function authenticateClient(): void
    {
        $token = new UsernamePasswordToken('admin', null, 'main', ['ROLE_ADMIN']);

        $session = $this->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
