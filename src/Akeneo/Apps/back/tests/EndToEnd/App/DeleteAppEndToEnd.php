<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd\App;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\DeleteAppCommand;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteAppEndToEnd extends ApiTestCase
{
    public function test_it_deletes_the_app_and_client_and_user()
    {
        $createAppCommand = new CreateAppCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        $this->get('akeneo_app.application.handler.create_app')->handle($createAppCommand);

        $findAnAppQuery = new FindAnAppQuery('magento');
        $app = $this->get('akeneo_app.application.handler.find_an_app')->handle($findAnAppQuery);

        $apiClient = $this->createAuthenticatedClient([], [], $app->clientId(), $app->secret(), 'magento', 'magento');
        $apiClient->request('GET', 'api/rest/v1/attributes');

        $this->get('doctrine.orm.entity_manager')->clear();

        $deleteAppCommand = new DeleteAppCommand('magento');
        $this->get('akeneo_app.application.handler.delete_app')->handle($deleteAppCommand);

        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $apiClient->getResponse()->getStatusCode());

        $countApp = $this->countApp($app->code());
        Assert::assertEquals(0, $countApp);

        $countClient = $this->countClient($app->clientId());
        Assert::assertEquals(0, $countClient);

        $countUser = $this->countUser($app->label());
        Assert::assertEquals(0, $countUser);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function countApp(string $appCode): int
    {
        $dbalConnection = $this->get('database_connection');

        $selectSql = <<<SQL
SELECT count(code)
FROM akeneo_app
WHERE code = :code
SQL;

        $stmt = $dbalConnection->executeQuery(
            $selectSql,
            [
                'code' => $appCode,
            ]
        );

        $count = $stmt->fetchColumn();

        return (int) $count;
    }

    private function countClient(string $clientId): int
    {
        $dbalConnection = $this->get('database_connection');

        $selectSql = <<<SQL
SELECT count(id)
FROM pim_api_client
WHERE id = :id
SQL;

        $stmt = $dbalConnection->executeQuery(
            $selectSql,
            [
                'id' => $clientId,
            ]
        );

        $count = $stmt->fetchColumn();

        return (int) $count;
    }

    private function countUser(string $username): int
    {
        $dbalConnection = $this->get('database_connection');

        $selectSql = <<<SQL
SELECT count(id)
FROM oro_user
WHERE username = :username
SQL;

        $stmt = $dbalConnection->executeQuery(
            $selectSql,
            [
                'username' => $username,
            ]
        );

        $count = $stmt->fetchColumn();

        return (int) $count;
    }
}
