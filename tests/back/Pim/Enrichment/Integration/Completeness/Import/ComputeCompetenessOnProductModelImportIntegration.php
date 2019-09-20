<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\Import;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use PHPUnit\Framework\Assert;

class ComputeCompetenessOnProductModelImportIntegration extends TestCase
{
    /**
     * @group critical
     */
    public function test_that_completeness_is_computed_when_product_models_are_imported(): void
    {
        $tshirtBlueM = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tshirt-divided-navy-blue-m');
        Assert::assertNull($tshirtBlueM->getValue('composition'));

        $this->assertCompleteness(
            $tshirtBlueM->getId(),
            [
                'ecommerce' => [
                    'en_US' => ['missing' => 3, 'ratio' => 72],
                    'de_DE' => ['missing' => 4, 'ratio' => 63],
                    'fr_FR' => ['missing' => 4, 'ratio' => 63],
                ],
                'mobile' => [
                    'en_US' => ['missing' => 4, 'ratio' => 60],
                    'de_DE' => ['missing' => 4, 'ratio' => 60],
                    'fr_FR' => ['missing' => 4, 'ratio' => 60],
                ],
                'print' => [
                    'en_US' => ['missing' => 4, 'ratio' => 63],
                    'de_DE' => ['missing' => 4, 'ratio' => 63],
                    'fr_FR' => ['missing' => 4, 'ratio' => 63],
                ],
            ]
        );

        $csv = <<<CSV
code;family_variant;parent;supplier;price-EUR;care_instructions;wash_temperature;color;composition
model-tshirt-divided;clothing_color_size;;zaro;20;Machine-washable;400;;
model-tshirt-divided-navy-blue;clothing_color_size;model-tshirt-divided;;;;;navy_blue;100% cotton
CSV;
        $jobLauncher = new JobLauncher(static::$kernel);
        $jobLauncher->launchAuthenticatedSubProcessImport('csv_catalog_modeling_product_model_import', $csv, 'Julia');

        $this->get('doctrine.orm.entity_manager')->clear();
        $tshirtBlueM = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tshirt-divided-navy-blue-m');
        Assert::assertNotNull($tshirtBlueM->getValue('composition'));
        Assert::assertSame('100% cotton', $tshirtBlueM->getValue('composition')->getData());

        $this->assertCompleteness(
            $tshirtBlueM->getId(),
            [
                'ecommerce' => [
                    'en_US' => ['missing' => 0, 'ratio' => 100],
                    'de_DE' => ['missing' => 1, 'ratio' => 90],
                    'fr_FR' => ['missing' => 1, 'ratio' => 90],
                ],
                'mobile' => [
                    'en_US' => ['missing' => 1, 'ratio' => 90],
                    'de_DE' => ['missing' => 1, 'ratio' => 90],
                    'fr_FR' => ['missing' => 1, 'ratio' => 90],
                ],
                'print' => [
                    'en_US' => ['missing' => 1, 'ratio' => 90],
                    'de_DE' => ['missing' => 1, 'ratio' => 90],
                    'fr_FR' => ['missing' => 1, 'ratio' => 90],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function assertCompleteness(int $productId, array $expectedCompletenesses): void
    {
        $actualCompletenesses = $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
                                     ->fromProductId((int)$productId);

        foreach ($expectedCompletenesses as $channelCode => $completenessesByChannel) {
            foreach ($completenessesByChannel as $localeCode => $expectedData) {
                $actualCompleteness = $actualCompletenesses->getCompletenessForChannelAndLocale(
                    $channelCode, $localeCode
                );
                Assert::assertNotNull($actualCompleteness);
                Assert::assertSame($expectedData['missing'], $actualCompleteness->missingCount());
                Assert::assertSame($expectedData['ratio'], $actualCompleteness->ratio());
            }
        }
    }
}
