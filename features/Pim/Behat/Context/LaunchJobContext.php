<?php

namespace Pim\Behat\Context;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;

/**
 * This context aims to contain all methods to the launch of a job.
 *
 * When we launch a job, we keep in this context the job instance and execution so that we can
 * check what happens in a next step. For instance, we want to check that 1 item has been skipped.
 *
 * This also allows to not use several time the same name of a job in the different steps of a use case.
 * It gives a more natural language.
 */
class LaunchJobContext extends PimContext implements SnippetAcceptingContext
{
    use SpinCapableTrait;

    /** @var JobInstance */
    private $jobInstance;

    /** @var JobExecution */
    private $jobExecution;

    /**
     * @When I import it via the job :jobName as :username
     */
    public function iImportItViaTheJobAs($jobName, $username)
    {
        $this->iImportItViaTheJobAsWithOptions($jobName, $username, new TableNode([]));
    }

    /**
     * @When I import it via the job :jobName as :username with options:
     */
    public function iImportItViaTheJobAsWithOptions($jobName, $username, TableNode $jobOptions)
    {
        $newJobOptions = new TableNode(
            array_merge($jobOptions->getTable(), [['filePath', self::$placeholderValues['%file to import%']]])
        );

        $this->jobInstance = $this->mainContext->getSubcontext('job')->theFollowingJobConfiguration($jobName, $newJobOptions);

        $user = $this->getFixturesContext()->getUser($username);

        $launcher = $this->mainContext->getContainer()->get('akeneo_batch.launcher.simple_job_launcher');
        $launcher->launch($this->jobInstance, $user);
    }

    /**
     * @When I wait for this job to finish
     */
    public function iWaitForThisJobToFinish()
    {
        $this->spin(function () {
            $this->jobInstance->getJobExecutions()->setInitialized(false);
            $this->getFixturesContext()->refresh($this->jobInstance);
            $this->jobExecution = $this->jobInstance->getJobExecutions()->last();
            $this->getFixturesContext()->refresh($this->jobExecution);

            return $this->jobExecution && !$this->jobExecution->isRunning();
        }, sprintf('The job execution of "%s" was too long', $this->jobInstance->getJobName()));
    }

    /**
     * @Then there should be :number product(s) skipped because there is no difference
     */
    public function thereShouldBeProductSkippedBecauseThereIsNoDifference($number)
    {
        $productsSkipped = array_map(function ($stepExecution) {
            return $stepExecution->getSummaryInfo('product_skipped_no_diff');
        }, $this->jobExecution->getStepExecutions()->toArray());

        Assert::assertEquals($productsSkipped[1], $number);
    }
}
