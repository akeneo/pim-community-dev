<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Export\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Ramsey\Uuid\Uuid;

abstract class AbstractProductExportTestCase extends TestCase
{
    protected JobLauncher $jobLauncher;
    private int $adminId;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->adminId = (int)$this->get('database_connection')->fetchOne(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => 'admin']
        );

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->createProduct(
            '8df9e79b-f95e-44a5-8b56-d961f2b34f08',
            [
                new SetIdentifierValue('sku', 'product_viewable_by_everybody_1'),
                new SetCategories(['categoryA2']),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'EN tablet'),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'FR tablet'),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'de_DE', 'DE tablet'),
                new SetNumberValue('a_number_float', null, null, '12.05'),
                new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
                new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
                new SetImageValue('a_localizable_image', null, 'de_DE', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
                new SetMeasurementValue('a_metric_without_decimal_negative', null, null, -10, 'CELSIUS'),
            ]
        );

        $this->createProduct(
            '1f434202-4c66-472a-9535-26cd17f1ebf9',
            [
                new SetIdentifierValue('sku', 'product_viewable_by_everybody_2'),
                new SetCategories(['categoryA2', 'categoryB']),
            ]
        );
        $this->createProduct(
            'ef2f1fdf-1548-4b0b-8cb4-f13b5646cb87',
            [
                new SetIdentifierValue('sku', 'product_not_viewable_by_redactor'),
                new SetCategories(['categoryB']),
            ]
        );
        $this->createProduct(
            '5dea2c67-818f-40e9-b732-bdcd22fd5f88',
            [
                new SetIdentifierValue('sku', 'product_without_category'),
                new AssociateProducts('X_SELL', ['product_viewable_by_everybody_2', 'product_not_viewable_by_redactor'])
            ]
        );
        $this->get('doctrine')->getManager()->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    /**
     * @param string $uuid
     * @param UserIntent[] $userIntents
     *
     * @return ProductInterface
     */
    protected function createProduct(string $uuid, array $userIntents = []) : ProductInterface
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->adminId,
                ProductUuid::fromUuid(Uuid::fromString($uuid)),
                $userIntents
            )
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->get('pim_catalog.repository.product')->find($uuid);
    }

    /**
     * @param string[] $associatedProductIdentifiers
     * @return string
     */
    protected function getExpectedAssociations(array $associatedProductIdentifiers): string
    {
        $result = $associatedProductIdentifiers;
        \usort($result, fn (string $id1, string $id2): int => $this->getProductUuid($id1)->compareTo($this->getProductUuid($id2)));

        return \join(',', $result);
    }
}
