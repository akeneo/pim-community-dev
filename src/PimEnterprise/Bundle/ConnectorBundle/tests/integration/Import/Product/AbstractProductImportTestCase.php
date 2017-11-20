<?php

namespace PimEnterprise\Bundle\ConnectorBundle\tests\integration\Import\Product;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\Warning;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractProductImportTestCase extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @return int
     */
    protected function countProduct(): int
    {
        return (int) $this->get('pim_catalog.repository.product')->countAll();
    }

    /**
     * @return int
     */
    protected function countProductDraft(): int
    {
        return (int) $this->get('pimee_workflow.repository.product_draft')->countAll();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     * @param array            $draftData
     *
     * @return ProductDraftInterface
     */
    protected function createProductDraft(
        ProductInterface $product,
        string $username,
        array $draftData
    ): ProductDraftInterface {
        $productDraft = $this->get('pimee_workflow.factory.product_draft')->createProductDraft($product, $username);
        $productDraft->setChanges($draftData);
        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_DRAFT);

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct(string $identifier): ProductInterface
    {
        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraftInterface
     */
    protected function getProductDraft(ProductInterface $product, string $username): ProductDraftInterface
    {
        return $this->get('pimee_workflow.repository.product_draft')->findUserProductDraft($product, $username);
    }

    /**
     * @param string $importCSV
     * @param string $username
     * @param array  $expected
     * @param int    $countProducts
     * @param int    $countDrafts
     * @param int    $countWarning
     * @param array  $expectedWarnings
     * @param int    $batchStatus
     */
    protected function assertImport(
        string $importCSV,
        ?string $username,
        array $expected,
        int $countProducts,
        int $countDrafts,
        int $countWarning,
        array $expectedWarnings = [],
        int $batchStatus = BatchStatus::COMPLETED
    ): void {
        $this->jobLauncher->launchSubProcessImport('csv_product_import', $importCSV, $username);
        $this->checkImport($expected, $countProducts, $countDrafts, $countWarning, $expectedWarnings, $batchStatus);
    }

    /**
     * @param string $importCSV
     * @param string $username
     * @param array  $expected
     * @param int    $countProducts
     * @param int    $countDrafts
     * @param int    $countWarning
     * @param array  $expectedWarnings
     * @param int    $batchStatus
     */
    protected function assertAuthenticatedImport(
        string $importCSV,
        ?string $username,
        array $expected,
        int $countProducts,
        int $countDrafts,
        int $countWarning,
        array $expectedWarnings = [],
        int $batchStatus = BatchStatus::COMPLETED
    ): void {
        $this->jobLauncher->launchAuthenticatedSubProcessImport('csv_product_import', $importCSV, $username);
        $this->checkImport($expected, $countProducts, $countDrafts, $countWarning, $expectedWarnings, $batchStatus);
    }

    /**
     * @param array $expected
     * @param int   $countProducts
     * @param int   $countDrafts
     * @param int   $countWarning
     * @param array $expectedWarnings
     * @param int   $batchStatus
     */
    private function checkImport(
        array $expected,
        int $countProducts,
        int $countDrafts,
        int $countWarning,
        array $expectedWarnings,
        int $batchStatus
    ): void {
        $this->get('doctrine')->getManager()->clear();
        $this->assertSame($countProducts, $this->countProduct());
        $this->assertSame($countDrafts, $this->countProductDraft());

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('admin');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        foreach ($expected as $identifier => $values) {
            $product = $this->getProduct($identifier);
            foreach ($values as $code => $value) {
                $codes = explode('-', $code);
                $attributeCode = $codes[0];
                $localeCode = isset($codes[1]) ? $codes[1] : null;
                $channelCode = isset($codes[2]) ? $codes[2] : null;
                $result = null !== $product->getValue($attributeCode, $localeCode, $channelCode)
                    ? $product->getValue($attributeCode, $localeCode, $channelCode)->getData()
                    : null;
                $this->assertSame($value, $result);
            }
        }

        $warnings = $this->get('doctrine')->getRepository(Warning::class)->findAll();
        $this->assertCount($countWarning, $warnings);
        foreach ($warnings as $i => $warning) {
            $this->assertSame($expectedWarnings[$i], $warning->getReason());
        }

        $jobExecution = $this->get('doctrine')->getRepository(JobExecution::class)->findAll();
        $this->assertCount(1, $jobExecution);
        $this->assertEquals(new BatchStatus($batchStatus), current($jobExecution)->getStatus());
    }
}
