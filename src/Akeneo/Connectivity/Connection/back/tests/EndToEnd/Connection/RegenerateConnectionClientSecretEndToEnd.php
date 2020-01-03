<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionClientSecretEndToEnd extends ApiTestCase
{
    public function test_it_disables_the_client_secret_and_tokens()
    {
        $createConnectionCommand = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        $connectionWithCredentials = $this->get('akeneo_connectivity.connection.application.handler.create_connection')->handle($createConnectionCommand);

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $connectionWithCredentials->clientId(),
            $connectionWithCredentials->secret(),
            $connectionWithCredentials->username(),
            $connectionWithCredentials->password()
        );
        $apiClient->request('GET', 'api/rest/v1/attributes');
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $regenerateConnectionSecretCommand = new RegenerateConnectionSecretCommand('magento');
        $this->get('akeneo_connectivity.connection.application.handler.regenerate_connection_secret')->handle($regenerateConnectionSecretCommand);

        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $apiClient->getResponse()->getStatusCode());
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
