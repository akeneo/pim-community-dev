<?php

namespace Pim\Behat\Context;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
     * @When /^i massively change the parent of the products (.*) for (.*)$/
     */
    public function iLaunchProductsMassEditJob(string $productCodes, string $newParentCode)
    {
        $productCodes = $this->getMainContext()->listToArray($productCodes);
        $productIds = $this->getProductIdsFromIdentifiers($productCodes);

        $this->launchJob($productIds, $newParentCode);
    }

    /**
     * @When /^i massively change the parent of the product models (.*) for (.*)$/
     */
    public function iLaunchProductModelsMassEditJob(string $productModelCodes, string $newParentCode)
    {
        $productModelCodes = $this->getMainContext()->listToArray($productModelCodes);
        $productModelIds = $this->getProductModelIdsFromIdentifiers($productModelCodes);

        $this->launchJob($productModelIds, $newParentCode);
    }

    private function launchJob(array $productIds, string $newParentCode)
    {
        $jobInstance = $this->mainContext->getSubcontext('job')->theFollowingJobConfiguration(self::MASS_CHANGE_PARENT_JOB_NAME, new TableNode([]));

        $user = $this->getFixturesContext()->getUser(self::USERNAME_FOR_JOB_LAUNCH);

        $jobExecutionConfiguration = $this->buildJobExecutionConfiguration($productIds, $newParentCode);

        $launcher = $this->mainContext->getContainer()->get('akeneo_batch.launcher.simple_job_launcher');
        $launcher->launch($jobInstance, $user, $jobExecutionConfiguration);

        $this->waitForJobToFinish($jobInstance);
    }

    private function buildJobExecutionConfiguration(array $productIds, string $newParentCode)
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
                    'field' => 'parent',
                    'value' => $newParentCode,
                ],
            ],
        ];
    }

    private function getProductIdsFromIdentifiers(array $productCodes)
    {
        $productIds = [];
        foreach ($productCodes as $productCode)
        {
            $product = $this->productRepository->findOneByIdentifier($productCode);
            $productIds[] = 'product_' . $product->getId();
        }

        return $productIds;
    }

    private function getProductModelIdsFromIdentifiers(array $productModelCodes)
    {
        $productModelIds = [];
        foreach ($productModelCodes as $productModelCode)
        {
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);
            $productModelIds[] = 'product_model_' . $productModel->getId();
        }

        return $productModelIds;
    }

    private function waitForJobToFinish(JobInstance $jobInstance): JobExecution
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
