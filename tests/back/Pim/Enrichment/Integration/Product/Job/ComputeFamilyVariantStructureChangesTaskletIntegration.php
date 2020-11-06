<?php


declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Job\ComputeFamilyVariantStructureChangesTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ComputeFamilyVariantStructureChangesTaskletIntegration extends TestCase
{
    /** @var ComputeFamilyVariantStructureChangesTasklet */
    private $computeFamilyVariantStructureChangesTasklet;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->authenticateUserAdmin();

        $jobParameters = new JobParameters(['family_variant_codes' => ['familyVariantA1']]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('a_step', $jobExecution);
        $this->computeFamilyVariantStructureChangesTasklet = $this->get('pim_catalog.tasklet.compute_family_variant_structure_changes');
        $this->computeFamilyVariantStructureChangesTasklet->setStepExecution($stepExecution);
    }

    /**
     * @test
     */
    public function it_compute_product_values_when_family_variant_structure_changes()
    {
        $rootProductModel = $this->createRootProductModel('familyVariantA1');
        $subProductModel = $this->createSubProductModel();
        $this->moveAttributeAtLevel1InFamilyVariant('familyVariantA1', 'a_number_float');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get('pim_catalog.validator.unique_value_set')->reset();
        $this->computeFamilyVariantStructureChangesTasklet->execute();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $rootProductModel = $this->getProductModel($rootProductModel->getCode());
        $subProductModel = $this->getProductModel($subProductModel->getCode());

        $this->assertNull($rootProductModel->getValue('a_number_float'));
        $this->assertNotNull($value = $subProductModel->getValue('a_number_float'));
        $this->assertEqualsCanonicalizing(5.5, $value->getData());
    }

    private function moveAttributeAtLevel1InFamilyVariant(string $familyVariantCode, string $attributeCode): void
    {
        $familyVariant = $this->getFamilyVariant($familyVariantCode);
        $content = $this->get('pim_catalog.normalizer.standard.family_variant')->normalize($familyVariant);
        $content['variant_attribute_sets'][0]['attributes'][] = $attributeCode;
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $content);
        $this->assertCount(0, $this->get('validator')->validate($familyVariant));
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    private function createRootProductModel(string $familyVariantCode): ProductModelInterface
    {
        return $this->createProductModel([
            'code' => 'root_product_model_code',
            'family_variant' => $familyVariantCode,
            'values' => [
                'a_number_float' => [['locale' => null, 'scope'  => null, 'data' => 5.5]],
            ],
            'categories' => ['categoryA1'],
        ]);
    }

    private function createSubProductModel(): ProductModelInterface
    {
        return $this->createProductModel([
            'code' => 'a_sub_product_model_code',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_product_model_code',
            'values' => [
                'a_simple_select' => [['locale' => null, 'scope'  => null, 'data' => 'optionB']],
            ],
            'categories' => ['categoryA2'],
        ]);
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        foreach ($errors as $error) {
            print_r($error->__toString() . "\n");
        }
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function getProductModel(string $code): ?ProductModelInterface
    {
        return $this->get('pim_api.repository.product_model')->findOneByIdentifier($code);
    }

    private function getFamilyVariant(string $code): ?FamilyVariant
    {
        return $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier($code);
    }

    private function authenticateUserAdmin(): void
    {
        $user = $this->get('pim_user.provider.user')->loadUserByUsername('admin');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }
}
