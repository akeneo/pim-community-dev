<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ProductSaverIntegration extends TestCase
{
    public function testProductCreationWithCategoryOnlyViewable()
    {
        $this->generateToken('mary');
        $this->saveProduct([new SetCategories(['categoryA1'])]);

        $this->assertCount(1, $this->get('pim_catalog.repository.product')->findAll());
    }

    public function testProductCreationWithCategoryOnlyEditable()
    {
        $this->generateToken('mary');
        $this->saveProduct([new SetCategories(['categoryA'])]);

        $this->assertCount(1, $this->get('pim_catalog.repository.product')->findAll());
    }

    public function testProductCreationWithCategoryOwner()
    {
        $this->generateToken('mary');
        $this->saveProduct([new SetCategories(['master'])]);

        $this->assertCount(1, $this->get('pim_catalog.repository.product')->findAll());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $username
     */
    private function generateToken($username)
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function saveProduct(array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('mary');

        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId('mary'), ProductIdentifier::fromIdentifier('sku'), $userIntents)
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);
        Assert::assertNotFalse($id);

        return \intval($id);
    }
}
