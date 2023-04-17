<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersioningControllerIntegration extends ControllerIntegrationTestCase
{

    public function test_it_returns_versioning_count(): void
    {
        $uuid = Uuid::uuid4();

        for ($i = 0; $i < 10; $i++) {
            $this->createProduct($uuid, [
                new SetBooleanValue('a_yes_no', null, null, true),
            ]);
            $this->createProduct($uuid, [
                new SetBooleanValue('a_yes_no', null, null, false),
            ]);
        }

        $this->logIn('julia');
        $this->callApiRoute(
            $this->client,
            'pim_enrich_product_history_rest_get',
            [
                'entityType' => 'product',
                'entityId' => $uuid->toString(),
            ],
            Request::METHOD_GET
        );

        $response = $this->client->getResponse();
        $content = \json_decode($response->getContent(), true);

        Assert::assertCount(20, $content);
    }

    public function test_it_returns_versioning_count_with_limit(): void
    {
        $uuid = Uuid::uuid4();

        for ($i = 0; $i < 250; $i++) {
            $this->createProduct($uuid, [
                new SetBooleanValue('a_yes_no', null, null, true),
            ]);
            $this->createProduct($uuid, [
                new SetBooleanValue('a_yes_no', null, null, false),
            ]);
        }

        $this->logIn('julia');
        $this->callApiRoute(
            $this->client,
            'pim_enrich_product_history_rest_get',
            [
                'entityType' => 'product',
                'entityId' => $uuid->toString(),
            ],
            Request::METHOD_GET
        );

        $response = $this->client->getResponse();
        $content = \json_decode($response->getContent(), true);

        Assert::assertCount(200, $content);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(UuidInterface $uuid, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithUuid(
            userId: $this->getUserId('admin'),
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }
}
