<?php

declare(strict_types=1);

namespace Pim\Behat\Context;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;

final class MassEditJobContext extends PimContext implements SnippetAcceptingContext
{
    use SpinCapableTrait;

    private const USERNAME_FOR_JOB_LAUNCH = 'admin';

    private const MASS_CHANGE_PARENT_JOB_NAME = 'change_parent_product';

    private $productRepository;

    private $productModelRepository;

    public function __construct(string $mainContextClass, IdentifiableObjectRepositoryInterface $productRepository, IdentifiableObjectRepositoryInterface $productModelRepository)
    {
        parent::__construct($mainContextClass);

        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * @param string $productCodes
     * @param string $newParentCode
     *
     * @When /^i massively change the parent of the products (.*) for (.*)$/
     */
    public function iMassivelyChangeTheParentOfTheProducts(string $productCodes, string $newParentCode)
    {
        $productCodes = $this->getMainContext()->listToArray($productCodes);
        $productIds = $this->getProductIdsFromIdentifiers($productCodes);

        $this->launchJob(self::MASS_CHANGE_PARENT_JOB_NAME, $productIds, 'parent', $newParentCode);
    }

    /**
     * @param string $productModelCodes
     * @param string $newParentCode
     *
     * @When /^i massively change the parent of the product models (.*) for (.*)$/
     */
    public function iMassivelyChangeTheParentOfTheProductModels(string $productModelCodes, string $newParentCode)
    {
        $productModelCodes = $this->getMainContext()->listToArray($productModelCodes);
        $productModelIds = $this->getProductModelIdsFromIdentifiers($productModelCodes);

        $this->launchJob(self::MASS_CHANGE_PARENT_JOB_NAME, $productModelIds, 'parent', $newParentCode);
    }

    /**
     * Launch the given mass edit job for all the products
     *
     * @param string $jobName
     * @param array $productIds
     * @param string $fieldName
     * @param string $newFieldValue
     *
     * @return void
     */
    private function launchJob(string $jobName, array $productIds, string $fieldName, string $newFieldValue): void
    {
        $jobInstance = $this->mainContext->getSubcontext('job')->theFollowingJobConfiguration($jobName, new TableNode([]));

        $user = $this->getFixturesContext()->getUser(self::USERNAME_FOR_JOB_LAUNCH);

        $jobExecutionConfiguration = $this->buildJobExecutionConfiguration($productIds, $fieldName, $newFieldValue);

        $launcher = $this->mainContext->getContainer()->get('akeneo_batch_queue.launcher.queue_job_launcher');
        $launcher->launch($jobInstance, $user, $jobExecutionConfiguration);

        $this->waitForJobToFinish($jobInstance);
    }

    /**
     * Build the job execution configuration array
     *
     * @param array $productIds
     * @param string $fieldName
     * @param string $newFieldValue
     *
     * @return array
     */
    private function buildJobExecutionConfiguration(array $productIds, string $fieldName, string $newFieldValue)
    {
        return [
            'filters' => [
                0 => [
                    'field' => 'id',
                    'operator' => 'IN',
                    'value' => $productIds,
                    'context' => [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ],
                ],
            ],
            'actions' => [
                0 => [
                    'field' => $fieldName,
                    'value' => $newFieldValue,
                ],
            ],
        ];
    }

    /**
     * Returns an array of products mysql IDs from an array of product codes
     *
     * @param array $productCodes
     *
     * @return array
     */
    private function getProductIdsFromIdentifiers(array $productCodes)
    {
        $productIds = [];
        foreach ($productCodes as $productCode) {
            $product = $this->productRepository->findOneByIdentifier($productCode);
            $productIds[] = 'product_' . $product->getId();
        }

        return $productIds;
    }

    /**
     * Returns an array of product model mysql IDs from an array of product model codes
     *
     * @param array $productModelCodes
     *
     * @return array
     */
    private function getProductModelIdsFromIdentifiers(array $productModelCodes)
    {
        $productModelIds = [];
        foreach ($productModelCodes as $productModelCode) {
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);
            $productModelIds[] = 'product_model_' . $productModel->getId();
        }

        return $productModelIds;
    }

    /**
     * @param JobInstance $jobInstance
     */
    private function waitForJobToFinish(JobInstance $jobInstance)
    {
        $jobInstance->getJobExecutions()->setInitialized(false);
        $this->getFixturesContext()->refresh($jobInstance);
        $jobExecution = $jobInstance->getJobExecutions()->last();

        $this->spin(function () use ($jobExecution) {
            $this->getFixturesContext()->refresh($jobExecution);

            return $jobExecution && !$jobExecution->isRunning();
        }, sprintf('The job execution of "%s" was too long', $jobInstance->getJobName()));

        return $jobExecution;
    }
}
