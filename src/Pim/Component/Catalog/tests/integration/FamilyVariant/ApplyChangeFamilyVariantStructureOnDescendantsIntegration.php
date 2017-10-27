<?php

namespace Pim\Component\Catalog\tests\integration\FamilyVariant;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ApplyChangeFamilyVariantStructureOnDescendantsIntegration extends TestCase
{
    /** @var JobLauncher */
    private $jobLauncher;

    protected function setUp()
    {
        parent::setUp();

        $this->purgeJobExecutions('compute_family_variant_structure_changes');
        $this->jobLauncher = $this->getFromTestContainer('akeneo_integration_tests.launcher.job_launcher');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    public function testMoveAttributeUpRemovesValuesOnOneLevel()
    {
        $product = $this->get('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('1111111287');

        $this->assertInstanceOf(
            ValueInterface::class,
            $product->getValuesForVariation()->getByCodes('weight')
        );

        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('shoes_size');

        // Put weight in common attributes
        $this->get('pim_catalog.updater.family_variant')
            ->update($familyVariant, [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => [
                            'size'
                        ],
                        'axes' => [
                            'eu_shoes_size'
                        ],
                    ],
                ],
            ]);

        $this->get('pim_catalog.saver.family_variant')
            ->save($familyVariant);

        while ($this->jobLauncher->hasJobInQueue()) {
            $this->jobLauncher->launchConsumerOnce();
        }

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('1111111287');

        $this->assertNull($product->getValuesForVariation()->getByCodes('weight'));
    }

    public function testMoveAttributeDownKeepsValuesOnOneLevel()
    {
        $product = $this->get('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('1111111287');

        $this->assertNull($product->getValuesForVariation()->getByCodes('name'));

        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('shoes_size');

        // Put the name on last level
        $this->get('pim_catalog.updater.family_variant')
            ->update($familyVariant, [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => [
                            'size',
                            'weight',
                            'name'
                        ],
                        'axes' => [
                            'eu_shoes_size'
                        ],
                    ],
                ],
            ]);

        $this->get('pim_catalog.saver.family_variant')
            ->save($familyVariant);

        while ($this->jobLauncher->hasJobInQueue()) {
            $this->jobLauncher->launchConsumerOnce();
        }

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('1111111287');

        $value = $product->getValuesForVariation()->getByCodes('name');
//        $value = $product->getValue('name');

        $this->assertInstanceOf(ValueInterface::class, $value);
        $this->assertSame('Brooks blue', $value->getData());

    }

    public function testMoveAttributeUpRemovesValuesOnTwoLevels()
    {
        $product = $this->get('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('running-shoes-m-antique-white');

        $this->get('pim_catalog.updater.product')->update($product, [
            'composition' => 'ham'
        ]);

        $this->get('pim_catalog.saver.product')->save($product);

        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('shoes_size_color');

        // Move the composition attribute from level 2 to level 1
        $this->get('pim_catalog.updater.family_variant')
            ->update($familyVariant, [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => [
                            'weight',
                            'variation_name',
                            'size',
                            'eu_shoes_size',
                            'composition'
                        ],
                        'axes' => [
                            'size'
                        ],
                    ],
                    [
                        'level' => 2,
                        'attributes' => [
                            'sku',
                            'image',
                            'variation_image',
                            'color',
                            'ean'
                        ],
                        'axes' => [
                            'color'
                        ],
                    ],
                ],
            ]);

        $this->get('pim_catalog.saver.family_variant')
            ->save($familyVariant);

        $this->jobLauncher->launchConsumerOnce();

        $product = $this->get('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('running-shoes-m-antique-white');

        $this->assertNull($product->getValuesForVariation()->getByCodes('weight'));
    }

    public function testMoveAttributeDownKeepsValuesOnTwoLevels()
    {

    }

    /**
     * Purges all the job executions for a job name.
     *
     * @param string $jobName
     */
    private function purgeJobExecutions(string $jobName): void
    {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')
            ->findOneBy(['code' => $jobName]);

        $jobExecutions = $jobInstance->getJobExecutions();
        foreach ($jobExecutions as $jobExecution) {
            $jobInstance->removeJobExecution($jobExecution);
        }

        $this->get('akeneo_batch.saver.job_instance')->save($jobInstance);
    }
}
