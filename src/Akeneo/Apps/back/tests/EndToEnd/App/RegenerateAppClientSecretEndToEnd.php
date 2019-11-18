<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd\App;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\RegenerateAppSecretCommand;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateAppClientSecretEndToEnd extends ApiTestCase
{
    public function test_it_disables_the_client_secret_and_tokens()
    {
        $createAppCommand = new CreateAppCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        $this->get('akeneo_app.application.handler.create_app')->handle($createAppCommand);

        $findAnAppQuery = new FindAnAppQuery('magento');
        $app = $this->get('akeneo_app.application.handler.find_an_app')->handle($findAnAppQuery);

        $apiClient = $this->createAuthenticatedClient([], [], $app->clientId(), $app->secret(), 'magento', 'magento');
        $apiClient->request('GET', 'api/rest/v1/attributes');
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $regenerateAppSecretCommand = new RegenerateAppSecretCommand('magento');
        $this->get('akeneo_app.application.handler.regenerate_app_secret')->handle($regenerateAppSecretCommand);

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
