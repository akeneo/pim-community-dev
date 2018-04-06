<?php

declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\Completeness;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks the completeness is computed whenever the required attributes of a family is changed.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateCompletenessOnFamilyUpdateIntegration extends AbstractCompletenessTestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    public function testComputeCompletenessesForProductWhenUpdatingAttributeRequirements()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('braided-hat-xxxl', 'ecommerce', 'fr_FR', 80);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'composition');
        $this->launchTimesAndWaitForJobExecutionsToEnd(1, 'compute_completeness_of_products_family');
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 1);
        $this->assertJobWasExecutedWithStatusAndJobParameters(
            'compute_completeness_of_products_family',
            BatchStatus::COMPLETED,
            ['family_code' => 'accessories']
        );
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 16);
        $this->assertCompleteness('braided-hat-xxxl', 'ecommerce', 'fr_FR', 66);
    }

    /**
     * Test update two families.
     */
    public function testComputeCompletenessesOnceForTwoSubsquentDifferentFamiliesUpdates()
    {
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 0);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->assertCompleteness('tshirt-unique-size-navy-blue', 'ecommerce', 'fr_FR', 54);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'composition');
        $this->updateFamilyPropertiesNotTriggeringCompletenessRecomputation('clothing');
        $this->launchTimesAndWaitForJobExecutionsToEnd(2, 'compute_completeness_of_products_family');
        $this->assertJobWasExecutedTimes('compute_completeness_of_products_family', 2);
        $this->assertJobWasExecutedWithStatusAndJobParameters(
            'compute_completeness_of_products_family',
            BatchStatus::COMPLETED,
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
        $this->jobLauncher = new JobLauncher(static::$kernel);
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
        $washAttribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('wash_temperature');
        $variationImageAttribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('variation_image');
        $erpNameAttribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('erp_name');

        $family->addAttribute($washAttribute);
        $family->addAttribute($variationImageAttribute);
        $family->addAttribute($erpNameAttribute);

        $family->setAttributeAsLabel($erpNameAttribute);
        $family->setAttributeAsImage($variationImageAttribute);

        $errors = $this->get('validator')->validate($family);
        static::assertCount(0, $errors);
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
        $this->get('doctrine.orm.default_entity_manager')->clear();
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
     * @param int    $batchStatus
     * @param array  $jobParameters
     */
    private function assertJobWasExecutedWithStatusAndJobParameters(string $jobName, int $batchStatus, array $jobParameters = [])
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $found = false;
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);
        foreach ($jobInstance->getJobExecutions() as $jobExecution) {
            $executedJobParameters = $jobExecution->getRawParameters();
            $diff = array_merge(
                array_diff($executedJobParameters, $jobParameters),
                array_diff($jobParameters, $executedJobParameters)
            );

            if (0 === count($diff) &&
                $jobExecution->getStatus()->getValue() === $batchStatus
            ) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Job execution with job parameters and status given not found.');
    }

    /**
     * Launches the  Wait for all the job executions of the given jobName to finnish within a given timeout.
     *
     * @param int    $times
     * @param string $jobName
     */
    private function launchTimesAndWaitForJobExecutionsToEnd(int $times, string $jobName)
    {
        for ($i = 0; $i < $times; $i++) {
            $this->jobLauncher->launchConsumerOnce();
        }

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
