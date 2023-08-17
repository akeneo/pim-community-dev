<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Import\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractProductImportTestCase extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
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
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents =  [], string $userName = 'admin'): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($userName);

        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId($userName), ProductIdentifier::fromIdentifier($identifier), $userIntents)
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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

    /**
     * @param ProductInterface $product
     * @param string           $username
     * @param array            $draftData
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createEntityWithValuesDraft(
        ProductInterface $product,
        string $username,
        array $draftData
    ): EntityWithValuesDraftInterface {
        $user = $this->get('pim_user.provider.user')->loadUserByUsername($username);
        $draftSource = $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user);
        $productDraft = $this->get('pimee_workflow.factory.product_draft')->createEntityWithValueDraft($product, $draftSource);
        $productDraft->setChanges($draftData);


        $values = [];
        foreach ($draftData['values'] as $code => $value) {
            $attribute = $this->get('akeneo.pim.structure.query.get_attributes')->forCode($code);
            foreach ($value as $data) {
                $values[] = $this->get('akeneo.pim.enrichment.factory.value')->createByCheckingData(
                    $attribute,
                    $data['scope'],
                    $data['locale'],
                    $data['data']
                );
            }
        }

        $productDraft->setValues(new WriteValueCollection($values));
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT);

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
     * @return EntityWithValuesDraftInterface
     */
    protected function getProductDraft(ProductInterface $product, string $username): EntityWithValuesDraftInterface
    {
        return $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($product, $username);
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
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
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
