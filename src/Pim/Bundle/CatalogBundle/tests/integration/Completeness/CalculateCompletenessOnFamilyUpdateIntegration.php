<?php

declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessTestCase;
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
    public function testComputeCompletenessForProductWhenUpdatingAttributeRequirements()
    {
        $this->assertJobWasNotExecuted('compute_completeness_of_products_family');
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'color');
        $this->assertJobWasExecutedOnce('compute_completeness_of_products_family', ['family_code' => 'accessories']);
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 33);
    }

    public function testDoesNotComputeCompletenessForProductsWhenNotUpdatingAttributeRequirements()
    {
        $this->assertJobWasNotExecuted('compute_completeness_of_products_family');
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->updateFamilyPropertiesNotTriggeringCompletenessRecomputation('accessories');
        $this->assertJobWasNotExecuted('compute_completeness_of_products_family');
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
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
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
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
     * Asserts a job instance has not run (no current job executions).
     *
     * @param string $jobName
     */
    private function assertJobWasNotExecuted(string $jobName): void
    {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);
        $this->assertEquals(
            0,
            $jobInstance->getJobExecutions()->count(),
            'Not expected job run: compute_completeness_of_products_family.'
        );
    }

    /**
     * Checks wether a job has been executed once or not.
     *
     * @param string $jobName
     * @param array  $expectedRawParameters
     */
    private function assertJobWasExecutedOnce(string $jobName, array $expectedRawParameters = []): void
    {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobName]);
        $jobExecutions = $jobInstance->getJobExecutions();
        $this->assertCount(1, $jobExecutions);
        $jobExecution = $jobExecutions->first();
        $this->assertSame($expectedRawParameters, $jobExecution->getRawParameters());
    }
}
