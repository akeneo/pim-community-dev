<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class FindNonExistingProductIdentifiersQueryIntegration extends TestCase
{
    /** @var FindNonExistingProductIdentifiersQueryInterface */
    private $findNonExistingProductIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
        $this->findNonExistingProductIdentifiersQuery = $this->get(
            'akeneo.pim.enrichment.product.query.find_non_existing_product_identifiers_query'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_return_nothing_when_nothing_passed()
    {
        self::assertEquals([], $this->findNonExistingProductIdentifiersQuery->execute([]));
    }

    /**
     * @test
     */
    public function it_returns_the_product_identifiers_that_does_not_exists()
    {
        $this->createProduct('product_1');
        $this->createProduct('product_2');
        $this->createProduct('product_3');
        $this->createProduct('product_4');
        $this->createProduct('product_5');

        $lookupProductIdentifiers = [
            'product_1',
            'product_2',
            'product_3',
            'product_does_not_exists',
        ];

        $actualNonExistingProductIdentifiers = $this->findNonExistingProductIdentifiersQuery->execute(
            $lookupProductIdentifiers
        );
        $expectedNonExistingProductIdentifiers = [
            'product_does_not_exists',
        ];

        self::assertEquals(
            $actualNonExistingProductIdentifiers,
            $expectedNonExistingProductIdentifiers
        );
    }

    private function createProduct(string $productIdentifier): void
    {
        $this->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $productIdentifier,
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }

    private function getUserId(string $username): int
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

    private function logIn(string $username): void
    {
        $session = $this->get('session');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $session->set('_security_main', serialize($token));
        $session->save();
    }
}
