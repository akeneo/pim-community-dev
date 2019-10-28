<?php

declare(strict_types=1);

namespace Pim\Behat\Context;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;

/**
 * This context aims to contain all methods to the launch of a job.
 *
 * When we launch a job, we keep in this context the job instance and execution so that we can
 * check what happens in a next step (and yes, that means this class is completely stateful).
 * For instance, we want to check that 1 item has been skipped.
 *
 * This also allows to not use several time the same name of a job in the different steps of a use case.
 * It gives a more natural language.
 *
 * In this context, there is no notion of who launched the job. This is made on purpose, don't try to
 * introduce it later, it makes no sense. The goal is to test the imports and exports coming from an external
 * application, or required at the setup of the application. Those imports and exports are made by a CLI, not
 * by a human.
 */
final class ImportExportFileContext extends PimContext implements SnippetAcceptingContext
{
    use SpinCapableTrait;

    /** @var JobInstance */
    private $jobInstance;

    /** @var JobExecution */
    private $jobExecution;

    private const USERNAME_FOR_JOB_LAUNCH = 'admin';

    /**
     * @When /^the (.*) are imported via the job ([\w\_]+)$/
     */
    public function entitiesAreImportedViaTheJob(string $entities, string $jobName)
    {
        $this->entitiesAreImportedViaTheJobWithOptions($entities, $jobName, new TableNode([]));
    }

    /**
     * @When /^the (.*) are imported via the job ([\w\_]+) with options:$/
     */
    public function entitiesAreImportedViaTheJobWithOptions(string $entities, string $jobName, TableNode $jobOptions)
    {
        $newJobOptions = new TableNode(
            array_merge($jobOptions->getTable(), [['filePath', self::$placeholderValues['%file to import%']]])
        );

        $this->launchJob($jobName, $newJobOptions);
    }

    /**
     * @When /^the (.*) are exported via the job ([\w\_]+)$/
     */
    public function entitiesAreExportedViaTheJob(string $entities, string $jobName)
    {
        $this->launchJob($jobName, new TableNode([]));
    }

    /**
     * @Then there should be :number product(s) skipped because there is no difference
     */
    public function thereShouldBeProductSkippedBecauseThereIsNoDifference(string $number)
    {
        $productsSkipped = array_map(function ($stepExecution) {
            return $stepExecution->getSummaryInfo('product_skipped_no_diff');
        }, $this->jobExecution->getStepExecutions()->toArray());

        Assert::assertEquals($productsSkipped[1], $number);
    }

    /**
     * @Then /^I should have the error "(?P<error>(?:[^"]|\\")*)"$/
     */
    public function iShouldHaveTheError(string $error)
    {
        foreach ($this->jobExecution->getStepExecutions() as $stepExecution) {
            foreach ($stepExecution->getWarnings() as $warning) {
                if (str_replace('\\"', '"', $error) === trim($warning->getReason())) {
                    return true;
                }
            }
        }

        throw new \Exception(sprintf('Cannot find the error "%s"', $error));
    }

    private function launchJob(string $jobName, TableNode $newJobOptions)
    {
        $this->jobInstance = $this->mainContext
            ->getSubcontext('job')
            ->theFollowingJobConfiguration($jobName, $newJobOptions);

        $user = $this->getFixturesContext()->getUser(self::USERNAME_FOR_JOB_LAUNCH);

        $launcher = $this->getService('akeneo_batch_queue.launcher.queue_job_launcher');
        $launcher->launch($this->jobInstance, $user);

        $this->jobExecution = $this->waitForJobToFinish($this->jobInstance);
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
