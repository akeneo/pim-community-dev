<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\PimConfigurationLoader;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetOpenIdPublicKeyEndToEnd extends WebTestCase
{
    private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler;
    private PimConfigurationLoader $pimConfigurationLoader;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pimConfigurationLoader = $this->get(PimConfigurationLoader::class);
        $this->generateAsymmetricKeysHandler = $this->get(GenerateAsymmetricKeysHandler::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_the_openid_public_key(): void
    {
        $this->pimConfigurationLoader->addPimconfiguration(
            SaveAsymmetricKeysQuery::OPTION_CODE,
            [AsymmetricKeys::PUBLIC_KEY => 'the_public_key', AsymmetricKeys::PRIVATE_KEY => 'the_private_key']
        );

        $this->client->request(
            'GET',
            '/connect/apps/v1/openid/public-key',
        );
        $result = $this->client->getResponse()->getContent();

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals('{"public_key":"the_public_key"}', $result);
    }

    public function test_the_openid_public_key_can_be_read_by_openssl(): void
    {
        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());
        $this->client->request(
            'GET',
            '/connect/apps/v1/openid/public-key',
        );
        $result = $this->client->getResponse()->getContent();

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $result = \json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        $publicKey = \openssl_pkey_get_public($result['public_key']);
        Assert::assertNotFalse($publicKey);
        $details = \openssl_pkey_get_details($publicKey);
        Assert::assertNotFalse($details);
        Assert::assertEquals(OPENSSL_KEYTYPE_RSA, $details['type']);
    }

    public function test_it_gets_an_error_if_there_is_no_openid_public_key_into_database(): void
    {
        $this->resetPimConfiguration();

        $this->client->request(
            'GET',
            '/connect/apps/v1/openid/public-key',
        );
        $result = $this->client->getResponse()->getContent();

        Assert::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(OpenIdKeysNotFoundException::MESSAGE, \json_decode($result, true, 512, JSON_THROW_ON_ERROR));
    }

    private function resetPimConfiguration(): void
    {
        $this->connection->executeQuery('TRUNCATE pim_configuration');
    }
}
