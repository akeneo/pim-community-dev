<?php

namespace Pim\Behat\Context;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Model\JobInstance;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\ConstraintViolationInterface;

class JobContext extends PimContext
{
    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Given /^the following job "([^"]*)" configuration:$/
     */
    public function theFollowingJobConfiguration($code, TableNode $table)
    {
        $jobInstance   = $this->getFixturesContext()->getJobInstance($code);
        $configuration = $jobInstance->getRawConfiguration();

        foreach ($table->getRowsHash() as $property => $value) {
            $value = $this->replacePlaceholders($value);
            if (in_array($value, ['yes', 'no'])) {
                $value = 'yes' === $value;
            }

            $configuration[$property] = $value;
        }

        /** @var ConnectorRegistry $connectorRegistry */
        $connectorRegistry = $this->getMainContext()->getContainer()->get('akeneo_batch.connectors');
        $job = $connectorRegistry->getJob($jobInstance);

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
                    print_r($jobParams->getParameters(), true),
                    $job->getName(),
                    implode(', ', $messages)
                )
            );
        }
        $jobInstance->setRawConfiguration($jobParams->getParameters());

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
     * @return \Behat\Behat\Context\Step\Then
     *
     * @When /^I should not be able to (launch|edit) the ("([^"]*)" (export|import) job)$/
     */
    public function iShouldNotBeAbleToAccessTheJob($action, JobInstance $job)
    {
        $this->currentPage = sprintf("%s %s", ucfirst($job->getType()), $action);
        $page              = $this->getCurrentPage()->open(['id' => $job->getId()]);

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
     * @param string $code
     *
     * @return string
     */
    public function getJobInstancePath($code)
    {
        $archives = $this->getJobInstanceArchives($code);
        $filePath = current($archives);
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
     * @return array
     */
    protected function getJobInstanceArchives($code)
    {
        $jobInstance = $this->getFixturesContext()->getJobInstance($code);
        $jobExecution = $jobInstance->getJobExecutions()->first();
        if (false === $jobExecution) {
            throw new \InvalidArgumentException(sprintf('No job execution found for job with code "%s"', $code));
        }

        $archiver = $this->getMainContext()->getContainer()->get('pim_base_connector.archiver.file_writer_archiver');
        $archives = $archiver->getArchives($jobExecution);

        return $archives;
    }
}
