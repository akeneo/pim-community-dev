<?php

namespace AkeneoTest\Pim\Enrichment\Integration\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Jobs\JobExecutionObserver;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChangeVariantFamilyStructureIntegration extends TestCase
{
    /** @var JobExecutionObserver */
    private $jobExecutionObserver;

    /** @var JobLauncher */
    private $jobLauncher;

    public function testMoveAttributeUpRemovesValuesOnOneLevel()
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('1111111287');
        $this->assertInstanceOf(ValueInterface::class, $product->getValuesForVariation()->getByCodes('weight'));

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('shoes_size');

        // Put weight in common attributes
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'level'      => 1,
                        'attributes' => [
                            'size',
                        ],
                        'axes'       => [
                            'eu_shoes_size',
                        ],
                    ],
                ],
            ]
        );

        $violationList = $this->get('validator')->validate($familyVariant);
        if (0 !== $violationList->count()) {
            throw new \LogicException('The family is not valid');
        }

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('1111111287');

        $this->assertNull($product->getValuesForVariation()->getByCodes('weight'));

        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );
    }

    public function testMoveAttributeDownKeepsValuesOnOneLevel()
    {
        $product = $this->get('pim_catalog.repository.product')
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

        $violationList = $this->get('validator')->validate($familyVariant);
        if (0 !== $violationList->count()) {
            throw new \LogicException('The family is not valid');
        }

        $this->get('pim_catalog.saver.family_variant')
            ->save($familyVariant);

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('1111111287');

        $value = $product->getValuesForVariation()->getByCodes('name', null, 'en_US');

        $this->assertInstanceOf(ValueInterface::class, $value);
        $this->assertSame('Brooks blue', $value->getData());

        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );
    }

    public function testMoveAttributeUpRemovesValuesOnTwoLevels()
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'running-shoes-m-antique-white',
            userIntents: [
                new SetTextareaValue('composition', null, null, 'ham')
            ]
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

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

        $violationList = $this->get('validator')->validate($familyVariant);
        if (0 !== $violationList->count()) {
            throw new \LogicException('The family is not valid');
        }

        $this->get('pim_catalog.saver.family_variant')
            ->save($familyVariant);

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('running-shoes-m-antique-white');

        $this->assertNull($product->getValuesForVariation()->getByCodes('composition'));

        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );
    }

    public function testMoveAttributeDownKeepsValuesOnTwoLevels()
    {
        $product = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('running-shoes-m-antique-white');

        $this->assertNull($product->getValuesForVariation()->getByCodes('material'));

        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('shoes_size_color');

        // Move down the material attribute from level 0 to level 2
        $this->get('pim_catalog.updater.family_variant')
            ->update($familyVariant, [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => [
                            'weight',
                            'variation_name',
                            'size',
                            'eu_shoes_size'
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
                            'composition',
                            'ean',
                            'material'
                        ],
                        'axes' => [
                            'color'
                        ],
                    ],
                ],
            ]);

        $violationList = $this->get('validator')->validate($familyVariant);
        if (0 !== $violationList->count()) {
            throw new \LogicException('The family is not valid');
        }

        $this->get('pim_catalog.saver.family_variant')
            ->save($familyVariant);

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('running-shoes-m-antique-white');

        $value = $product->getValuesForVariation()->getByCodes('material');

        $this->assertInstanceOf(ValueInterface::class, $value);
        $this->assertThat($value->getData(), $this->isType('string'));
        $this->assertSame('leather', $value->getData());

        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );
    }

    public function testItDoesNotRunBackgroundJobWhenAttributesHaveNotChanged(): void
    {
        $this->assertCount(
            0,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('shoes_size_color');
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
        $this->get('doctrine.orm.default_entity_manager')->clear();
        $this->assertCount(
            0,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('shoes_size_color');
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'labels' => ['en_US' => 'test'],
        ]);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $this->get('doctrine.orm.default_entity_manager')->clear();
        $this->assertCount(
            0,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );
    }

    public function testItDoesNotRunBackgroundJobWhenAJobIsAlreadyCreated(): void
    {
        $this->assertCount(
            0,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('shoes_size');
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => ['size'],
                        'axes' => ['eu_shoes_size'],
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $this->get('doctrine.orm.default_entity_manager')->clear();
        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('shoes_size');
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => ['size', 'weight'],
                        'axes' => ['eu_shoes_size'],
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
        $this->get('doctrine.orm.default_entity_manager')->clear();
        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );
    }

    public function testBulkMoveAnAttributeFromItsLevelRunBackgroundJobs(): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('1111111287');
        $this->assertInstanceOf(ValueInterface::class, $product->getValuesForVariation()->getByCodes('weight'));

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('shoes_size');

        // Put weight in common attributes
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'attributes' => ['size'],
                        'axes' => ['eu_shoes_size'],
                    ],
                ],
            ]
        );

        $violationList = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $violationList, (string) $violationList);
        $this->get('pim_catalog.saver.family_variant')->saveAll([$familyVariant]);

        $this->assertCount(
            1,
            $this->jobExecutionObserver->jobExecutionsWithJobName('compute_family_variant_structure_changes')
        );

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('1111111287');
        $this->assertNull($product->getValuesForVariation()->getByCodes('weight'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeJobExecutions('compute_family_variant_structure_changes');
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
        $this->jobExecutionObserver = $this->get(
            'akeneo_integration_tests.launcher.job_execution_observer'
        );
        $this->jobExecutionObserver->purge('compute_family_variant_structure_changes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
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
