<?php

declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\Completeness;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks the completeness is computed whenever the required attributes of a family is changed.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateCompletenessOnFamilyUpdateIntegration extends AbstractCompletenessTestCase
{
    public function testComputeCompletenessesForProductWhenUpdatingAttributeRequirements()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('braided-hat-xxxl', 'ecommerce', 'fr_FR', 80);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'composition');
        $this->waitForJobExecutionsToEnd('compute_completeness_of_products_family');
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 1);
        $this->assertJobWasExecutedWithJobParameters(
            'compute_completeness_of_products_family',
            ['family_code' => 'accessories']
        );
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 16);
        $this->assertCompleteness('braided-hat-xxxl', 'ecommerce', 'fr_FR', 66);
    }

    public function testDoesNotComputeCompletenessesForProductsWhenNotUpdatingAttributeRequirements()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('braided-hat-xxxl', 'ecommerce', 'fr_FR', 80);
        $this->updateFamilyPropertiesNotTriggeringCompletenessRecomputation('accessories');
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('braided-hat-xxxl', 'ecommerce', 'fr_FR', 80);
    }

    public function testDoesNotComputeCompletenessesOnFamilyCreation()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->createFamilyWithRequirement(
            'new_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT
        );
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
    }

    /**
     * This case checks that running family updates concurrently does not break the computation of the completeness.
     * - Both family updates should trigger completeness recomputations
     */
    public function testComputeCompletenessesOfTwoSubsquentDifferentFamiliesUpdates()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('tshirt-unique-size-navy-blue', 'ecommerce', 'fr_FR', 54);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'composition');
        $this->addFamilyRequirement('clothing', 'ecommerce', 'price');
        $this->waitForJobExecutionsToEnd('compute_completeness_of_products_family');
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 2);
        $this->assertJobWasExecutedWithJobParameters(
            'compute_completeness_of_products_family',
            ['family_code' => 'accessories']
        );
        $this->assertJobWasExecutedWithJobParameters(
            'compute_completeness_of_products_family',
            ['family_code' => 'clothing']
        );
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 16);
        $this->assertCompleteness('tshirt-unique-size-navy-blue', 'ecommerce', 'fr_FR', 50);
    }

    /**
     * This case checks that running family updates concurrently does not break the computation of the completeness.
     * - One out of the two families should trigger the recomputations
     */
    public function testComputeCompletenessesOnceForTwoSubsquentDifferentFamiliesUpdates()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('tshirt-unique-size-navy-blue', 'ecommerce', 'fr_FR', 54);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'composition');
        $this->updateFamilyPropertiesNotTriggeringCompletenessRecomputation('clothing');
        $this->waitForJobExecutionsToEnd('compute_completeness_of_products_family');
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 1);
        $this->assertJobWasExecutedWithJobParameters(
            'compute_completeness_of_products_family',
            ['family_code' => 'accessories']
        );
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 16);
        $this->assertCompleteness('tshirt-unique-size-navy-blue', 'ecommerce', 'fr_FR', 54);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->purgeJobExecutions('compute_completeness_of_products_family');
    }

    /**
     * Purges all the job executions for a job name.
     *
     * @param string $jobName
     */
    private function purgeJobExecutions(string $jobName): void
    {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);

        $jobExecutions = $jobInstance->getJobExecutions();
        foreach ($jobExecutions as $jobExecution) {
            $jobInstance->removeJobExecution($jobExecution);
        }

        $this->get('akeneo_batch.saver.job_instance')->save($jobInstance);
    }

    /**
     * Return the completeness of a product for a channel and a locale.
     *
     * @param ProductInterface $product
     * @param string           $channelCode
     * @param string           $localeCode
     *
     * @return null|CompletenessInterface
     */
    private function getCompletenesses(
        ProductInterface $product,
        string $channelCode,
        string $localeCode
    ): ?CompletenessInterface {
        $completenesses = $product->getCompletenesses();

        foreach ($completenesses as $completeness) {
            if ($channelCode === $completeness->getChannel()->getCode() &&
                $localeCode === $completeness->getLocale()->getCode()) {
                return $completeness;
            }
        }

        return null;
    }

    /**
     * @param string $productIdentifier
     * @param string $channelCode
     * @param string $localeCode
     * @param int    $ratio
     */
    private function assertCompleteness($productIdentifier, $channelCode, $localeCode, $ratio): void
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);
        $completeness = $this->getCompletenesses($product, $channelCode, $localeCode);

        $this->assertNotNull($completeness);
        $this->assertEquals($ratio, $completeness->getRatio());
    }

    /**
     * Series of family updates that do not trigger completeness recalculations.
     *
     * @param string $familyCode
     */
    private function updateFamilyPropertiesNotTriggeringCompletenessRecomputation(
        string $familyCode
    ): void {
        $family = $this->get('pim_catalog.repository.family')->findOneByCode($familyCode);
        $this->get('pim_catalog.updater.family')->update($family, [
            'labels' => [
                'fr_FR' => 'New label',
            ],
            'attribute_as_image' => 'variation_image',
            'attribute_as_label' => 'erp_name',
            'attributes' => [
                'wash_temperature'
            ],
        ]);
        $this->get('validator')->validate($family);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * Checks whether a job has been executed a given number of times.
     *
     * @param string $jobName
     * @param int    $times
     */
    private function assertJobWasExecutedTimes(string $jobName, int $times): void
    {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);
        $jobExecutionsCount = $jobInstance->getJobExecutions()->count();
        $this->assertEquals(
            $times,
            $jobExecutionsCount,
            sprintf('Expected job to run %s times, ran %s.', $times, $jobExecutionsCount)
        );
    }

    /**
     * @param string $jobName
     * @param array $jobParameters
     */
    private function assertJobWasExecutedWithJobParameters(string $jobName, array $jobParameters = [])
    {
        $found = false;
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);
        foreach ($jobInstance->getJobExecutions() as $jobExecution) {
            $executedJobParameters = $jobExecution->getRawParameters();
            $diff = array_merge(
                array_diff($executedJobParameters, $jobParameters),
                array_diff($jobParameters, $executedJobParameters)
            );

            if (0 === count($diff)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Job execution with job parameters given not found.');
    }

    /**
     * Wait for all the job executions of the given jobName to finnish within a given timeout.
     *
     * @param string $jobName
     */
    private function waitForJobExecutionsToEnd(string $jobName)
    {
        $maxRetry = 30;
        for ($retry = 0; $retry < $maxRetry; $retry++) {
            sleep(1);
            if ($this->areJobExecutionsEnded($jobName)) {
                return;
            }
        }
    }

    /**
     * Checks whether all the jobExecutions for the given jobName.
     *
     * @param string $jobName
     *
     * @return bool
     */
    private function areJobExecutionsEnded(string $jobName): bool
    {
        $jobExecutionsEnded = true;

        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);
        $jobExecutions = $jobInstance->getJobExecutions();

        foreach ($jobExecutions as $jobExecution) {
            $jobExecutionStatus = $jobExecution->getStatus();
            if ($jobExecutionStatus->isRunning() || $jobExecutionStatus->isStarting()) {
                $jobExecutionsEnded = false;
            }
        }

        return $jobExecutionsEnded;
    }
}
