<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Connection\WrongCredentialsCombination;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombinations;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Persistence\DbalWrongCredentialsCombinationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveWrongCredentialsConnectionEndToEnd extends ApiTestCase
{
    public function test_that_authentication_with_good_combination_does_not_save_wrong_credentials(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request(
            'POST',
            'api/oauth/v1/token',
            [
                'username'   => $apiConnection->username(),
                'password'   => $apiConnection->password(),
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $apiConnection->clientId(),
                'PHP_AUTH_PW'   => $apiConnection->secret(),
                'CONTENT_TYPE'  => 'application/json',
            ]
        );
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $wrongCredentialsCombinations = $this->findAllWrongCredentialsCombination();
        Assert::assertInstanceOf(WrongCredentialsCombinations::class, $wrongCredentialsCombinations);
        Assert::assertEmpty($wrongCredentialsCombinations->normalize());
    }

    public function test_that_wrong_credentials_combination_is_saved_after_authentication(): void
    {
        $magentoConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);
        $bynderConnection = $this->createConnection('bynder', 'Magento', FlowType::DATA_DESTINATION);

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request(
            'POST',
            'api/oauth/v1/token',
            [
                'username'   => $magentoConnection->username(),
                'password'   => $magentoConnection->password(),
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $bynderConnection->clientId(),
                'PHP_AUTH_PW'   => $bynderConnection->secret(),
                'CONTENT_TYPE'  => 'application/json',
            ]
        );
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        $wrongCredentialsCombinations = $this->findAllWrongCredentialsCombination();
        Assert::assertInstanceOf(WrongCredentialsCombinations::class, $wrongCredentialsCombinations);
        $normalizedResult = $wrongCredentialsCombinations->normalize();
        Assert::assertArrayHasKey('bynder', $normalizedResult);
        Assert::assertCount(1, $normalizedResult['bynder']['users']);
        Assert::assertSame($magentoConnection->username(), $normalizedResult['bynder']['users'][0]['username']);
    }

    private function findAllWrongCredentialsCombination(): WrongCredentialsCombinations
    {
        $repository = $this->get(DbalWrongCredentialsCombinationRepository::class);

        return $repository->findAll(new \DateTimeImmutable('now - 1 day', new \DateTimeZone('UTC')));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
