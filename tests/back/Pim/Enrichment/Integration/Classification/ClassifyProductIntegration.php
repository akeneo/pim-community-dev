<?php


namespace AkeneoTest\Pim\Enrichment\Integration\Classification;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ClassifyProductIntegration extends TestCase
{
    public function testClassify()
    {
        $this->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'tee',
            userIntents: [
                new SetFamily('clothing'),
                new SetCategories(['supplier_zaro'])
            ]
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tee');
        $this->assertCount(1, $product->getCategories());
        $category = $product->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
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
