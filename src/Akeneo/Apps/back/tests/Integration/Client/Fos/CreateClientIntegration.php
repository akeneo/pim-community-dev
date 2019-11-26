<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Client\Fos;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Model\Read\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class CreateClientIntegration extends TestCase
{
    /** @var CreateClientInterface */
    private $createClient;

    public function test_that_it_creates_a_client()
    {
        $client = $this->createClient->execute('Magento connector');

        Assert::assertInstanceOf(Client::class, $client);
        Assert::assertGreaterThan(0, $client->id());
        Assert::assertIsString($client->clientId());
        Assert::assertIsString($client->secret());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createClient = $this->get('akeneo_app.service.client.create_client');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
