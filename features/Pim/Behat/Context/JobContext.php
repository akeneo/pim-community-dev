<?php

namespace Pim\Behat\Context;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobInstance;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Symfony\Component\Validator\ConstraintViolationInterface;

class JobContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Given /^the following job "([^"]*)" configuration:$/
     */
    public function theFollowingJobConfiguration($code, TableNode $table)
    {
        $jobInstance   = $this->getFixturesContext()->getJobInstance($code);
        $configuration = $jobInstance->getRawParameters();

        foreach ($table->getRowsHash() as $property => $value) {
            $value = $this->replacePlaceholders($value);
            if (in_array($value, ['yes', 'no'])) {
                $value = 'yes' === $value;
            }

            if ('filters' === $property) {
                $value = json_decode($value, true);
            }

            $configuration[$property] = $value;
        }

        /** @var JobRegistry $jobRegistry */
        $jobRegistry = $this->getMainContext()->getContainer()->get('akeneo_batch.job.job_registry');
        $job = $jobRegistry->get($jobInstance->getJobName());

        /** @var JobParametersFactory $jobParamsFactory */
        $jobParamsFactory = $this->getMainContext()->getContainer()->get('akeneo_batch.job_parameters_factory');
        $jobParams = $jobParamsFactory->create($job, $configuration);

        /** @var JobParametersValidator $jobParamsValidator */
        $jobParamsValidator = $this->getMainContext()->getContainer()->get('akeneo_batch.job.job_parameters_validator');
        $violations = $jobParamsValidator->validate($job, $jobParams, ['Default']);

        if ($violations->count() > 0) {
            $messages = [];
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(
                sprintf(
                    'The parameters "%s" are not valid for the job "%s" due to violations "%s"',
                    print_r($jobParams->all(), true),
                    $job->getName(),
                    implode(', ', $messages)
                )
            );
        }
        $jobInstance->setRawParameters($jobParams->all());

        $saver = $this->getMainContext()->getContainer()->get('akeneo_batch.saver.job_instance');
        $saver->save($jobInstance);
    }

    /**
     * @param string $type
     *
     * @When /^I launch the (import|export) job$/
     */
    public function iExecuteTheJob($type)
    {
        $this->wait();
        $this->getPage(sprintf('%s show', ucfirst($type)))->execute();
    }

    /**
     * @param string $file
     * @param string $field
     *
     * @Given /^I attach file "([^"]*)" to "([^"]*)"$/
     */
    public function attachFileToField($file, $field)
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR)
                .DIRECTORY_SEPARATOR.$file;
            if (is_file($fullPath)) {
                $file = $fullPath;
            }
        }

        $this->getCurrentPage()->attachFileToField($field, $file);
        $this->getMainContext()->wait();
    }

    /**
     * @param string      $action
     * @param JobInstance $job
     *
     * @return Step\Then
     *
     * @When /^I should not be able to (launch|edit) the ("([^"]*)" (export|import) job)$/
     */
    public function iShouldNotBeAbleToAccessTheJob($action, JobInstance $job)
    {
        $this->currentPage = sprintf("%s %s", ucfirst($job->getType()), $action);
        $this->getCurrentPage()->open(['id' => $job->getId()]);

        return new Step\Then('I should see "403 Forbidden"');
    }

    /**
     * @param string $jobType
     *
     * @Given /^I try to create an unknown (import|export)$/
     */
    public function iTryToCreateAnUnknownJob($jobType)
    {
        $this->getNavigationContext()->openPage(sprintf('%s creation', ucfirst($jobType)));
    }

    /**
     * @param string $label
     * @param string $message
     *
     * @Then /^the export content field "([^"]*)" should contain "([^"]*)"$/
     */
    public function theExportContentFieldShouldContain($label, $message)
    {
        $page = $this->getCurrentPage();
        $field = $this->spin(function () use ($label, $page) {
            return $page->find('css', sprintf('[data-name="%s"] .select2-default', $label));
        }, sprintf('Field "%s" not found.', $label));

        assertEquals($field->getValue(), $message);
    }

    /**
     * @param string $filters
     *
     * @Then /^I should see the ordered filters (.*)$/
     */
    public function iShouldSeeTheOrderedFilters($filters)
    {
        $this->spin(function () use ($filters) {
            $expectedOrderedFilters = $this->getMainContext()->listToArray($filters);
            $currentOrderedFilters = $this->getOrderedFilters();

            return $expectedOrderedFilters === $currentOrderedFilters;
        }, 'Filters not ordered.');
    }

    /**
     * @param string   $code
     * @param int|null $number
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getJobInstancePath($code, $number = null)
    {
        $archives = $this->getJobInstanceArchives($code);
        $filePath = null;

        if (null === $number) {
            $filePath = end($archives);
        } else {
            foreach ($archives as $keyArchive => $path) {
                if (0 === strpos($keyArchive, sprintf('%s_%s.', $code, $number))) {
                    $filePath = $path;
                }
            }

            if (null === $filePath) {
                throw new \Exception(sprintf('There is no file number %d in generated archive', $number));
            }
        }

        $archivePath = $this->getMainContext()->getContainer()->getParameter('archive_dir');

        return sprintf('%s%s%s', $archivePath, DIRECTORY_SEPARATOR, $filePath);
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getJobInstanceFilename($code)
    {
        $archives = $this->getJobInstanceArchives($code);
        $filename = key($archives);

        return $filename;
    }

    /**
     * @param string $code
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAllJobInstancePaths($code)
    {
        $archivesCount = count($this->getJobInstanceArchives($code));
        $filePaths = [];

        for ($i = 1; $i < $archivesCount + 1; $i++) {
            $filePaths[] = $this->getJobInstancePath($code, $i);
        }

        return $filePaths;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    protected function getJobInstanceArchives($code)
    {
        $jobInstance = $this->getFixturesContext()->getJobInstance($code);
        $this->getFixturesContext()->refresh($jobInstance);
        $jobExecution = $jobInstance->getJobExecutions()->last();
        if (false === $jobExecution) {
            throw new \InvalidArgumentException(sprintf('No job execution found for job with code "%s"', $code));
        }

        $archiver = $this->getMainContext()->getContainer()->get('pim_connector.archiver.file_writer_archiver');
        $archives = $archiver->getArchives($jobExecution);

        return $archives;
    }

    /**
     * Gets currently displayed export filters, ordered.
     *
     * return string[]
     */
    protected function getOrderedFilters()
    {
        $filters = $this->getCurrentPage()->findAll('css', '.filters .filter-item');
        $currentFilters = [];

        foreach ($filters as $filter) {
            $currentFilters[] = $filter->getAttribute('data-name');
        }

        return $currentFilters;
    }
}
