<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Client\Fos;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class CreateClientIntegration extends TestCase
{
    /** @var CreateClientInterface */
    private $createClient;

    public function test_that_it_creates_a_client()
    {
        $clientId = $this->createClient->execute('Magento connector');

        Assert::assertInstanceOf(ClientId::class, $clientId);
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
