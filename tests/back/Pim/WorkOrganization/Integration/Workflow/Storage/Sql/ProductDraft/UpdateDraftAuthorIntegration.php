<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Storage\Sql\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class UpdateDraftAuthorIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, [
            'username' => 'admin',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'adMinPasSWord',
            'email' => 'admin@example.com',
            'roles' => ['ROLE_ADMINISTRATOR'],
            'groups' => ['Manager'],
            'enabled' => true,
        ]);
        $this->get('pim_user.saver.user')->save($user);
    }

    public function testQueryToGetAssociatedProductCodes(): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.saver.product')->save($product);

        $draftSource = $this
            ->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')
            ->createFromUser($this->get('pim_user.provider.user')->loadUserByUsername('admin'));

        $draft = $this->get('pimee_workflow.factory.product_draft')->createEntityWithValueDraft(
            $product,
            $draftSource
        );
        $draft->setValues(new WriteValueCollection());
        $this->get('pimee_workflow.saver.product_draft')->save($draft);

        $this->get('pimee_workflow.sql.product.update_draft_author')->execute('admin', 'new_admin');

        $connection = $this->get('database_connection');
        $result = $connection
            ->executeQuery('SELECT id FROM pimee_workflow_product_draft where author = "new_admin"')
            ->fetch(\PDO::FETCH_ASSOC);
        Assert::assertNotEmpty($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
