<?php

namespace Pim\Behat\Context\Domain\Collect;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

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
 * introduce it later, it makes no sense. The goal is to test the imports coming from an external application,
 * or required at the setup of the application. Those imports are made by a CLI, not by a human.
 */
final class ImportFileContext extends PimContext implements SnippetAcceptingContext
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
    public function entitiesAreImportedViaTheJob($entities, $jobName)
    {
        $this->entitiesAreImportedViaTheJobWithOptions($entities, $jobName, new TableNode([]));
    }

    /**
     * @When /^the (.*) are imported via the job ([\w\_]+) with options:$/
     */
    public function entitiesAreImportedViaTheJobWithOptions($entities, $jobName, TableNode $jobOptions)
    {
        $newJobOptions = new TableNode(
            array_merge($jobOptions->getTable(), [['filePath', self::$placeholderValues['%file to import%']]])
        );

        $this->jobInstance = $this->mainContext->getSubcontext('job')->theFollowingJobConfiguration($jobName, $newJobOptions);

        $user = $this->getFixturesContext()->getUser(self::USERNAME_FOR_JOB_LAUNCH);

        $launcher = $this->mainContext->getContainer()->get('akeneo_batch.launcher.simple_job_launcher');
        $launcher->launch($this->jobInstance, $user);

        $this->jobExecution = $this->waitForJobToFinish($this->jobInstance);
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

    /**
     * @Then /^I should have the warning "(?P<expectedWarning>(?:[^"]|\\")*)"$/
     */
    public function iShouldHaveTheWarning(string $expectedWarning)
    {
        foreach ($this->jobExecution->getStepExecutions() as $stepExecution) {
            foreach     ($stepExecution->getWarnings() as $warning) {
                if (str_replace('\\"', '"', $expectedWarning) === trim($warning->getReason())) {
                    return true;
                }
            }
        }

        throw new ExpectationException(sprintf('Cannot find the warning "%s".', $expectedWarning), $this->getSession());
    }

    /**
     * @Then /^I should have the error "(?P<expectedError>(?:[^"]|\\")*)"$/
     */
    public function iShouldHaveTheError(string $expectedError)
    {
        $expectedError = $this->replacePlaceholders($expectedError);

        foreach ($this->jobExecution->getStepExecutions() as $stepExecution) {
            foreach ($stepExecution->getFailureExceptions() as $exception) {
                if (str_replace('\\"', '"', $expectedError) === trim($exception['message'])) {
                    return true;
                }
            }
        }

        throw new ExpectationException(sprintf('Cannot find the error "%s".', $expectedError), $this->getSession());
    }

    /**
     * @When the file :filename is ready for import
     */
    public function theFileIsReadyForImport(string $filename)
    {
        $source = '%fixtures%' . $filename;
        $source = $this->replacePlaceholders($source);
        $source = realpath($source);

        $destination = '%tmp%/' . $filename;
        $destination = $this->replacePlaceholders($destination);

        @rmdir(dirname($destination));
        @mkdir(dirname($destination), 0777, true);
        @copy($source, $destination);
        if (!is_readable($destination)) {
            throw new \Exception(sprintf('The file "%s" is not ready to be imported.', $destination));
        }

        self::$placeholderValues['%file to import%'] = $destination;
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
