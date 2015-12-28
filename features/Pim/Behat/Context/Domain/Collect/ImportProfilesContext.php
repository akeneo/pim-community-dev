<?php

namespace Pim\Behat\Context\Domain\Collect;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Pim\Behat\Context\PimContext;

class ImportProfilesContext extends PimContext
{
    /**
     * @param string       $extension
     * @param PyStringNode $string
     *
     * @Given /^the following ([^"]*) file to import:$/
     */
    public function theFollowingFileToImport($extension, PyStringNode $string)
    {
        $extension = strtolower($extension);

        $string = $this->replacePlaceholders($string);

        $this->placeholderValues['%file to import%'] = $filename =
            sprintf(
                '%s/pim-import/behat-import-%s.%s',
                $this->placeholderValues['%tmp%'],
                substr(md5(rand()), 0, 7),
                $extension
            );
        @rmdir(dirname($filename));
        @mkdir(dirname($filename), 0777, true);

        file_put_contents($filename, (string) $string);
    }

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

        $jobInstance->setRawConfiguration($configuration);
        // TODO use a Saver
        $this->getFixturesContext()->flush();
    }

    /**
     * @param TableNode $table
     *
     *
     * @Given /^the following CSV configuration to import:$/
     */
    public function theFollowingCSVToImport(TableNode $table)
    {
        $delimiter = ';';

        $data    = $table->getRowsHash();
        $columns = implode($delimiter, array_keys($data));

        $rows = [];
        foreach ($data as $values) {
            foreach ($values as $index => $value) {
                $value          = in_array($value, ['yes', 'no']) ? (int) $value === 'yes' : $value;
                $rows[$index][] = $value;
            }
        }
        $rows = array_map(
            function ($row) use ($delimiter) {
                return implode($delimiter, $row);
            },
            $rows
        );

        array_unshift($rows, $columns);

        return $this->theFollowingFileToImport('csv', new PyStringNode(implode("\n", $rows)));
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
     *
     * @Given /^I upload and import the file "([^"]*)"$/
     */
    public function iUploadAndImportTheFile($file)
    {
        $this->getCurrentPage()->clickLink('Upload and import');
        $this->attachFileToField($this->replacePlaceholders($file), 'Drop a file or click here');
        $this->getCurrentPage()->pressButton('Upload and import now');

        sleep(10);
        $this->getMainContext()->reload();
        $this->getMainContext()->wait();
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
     * @param string $file
     *
     * @Given /^I upload and import an invalid file "([^"]*)"$/
     */
    public function iUploadAndImportAnInvalidFile($file)
    {
        $this->getCurrentPage()->clickLink('Upload and import');
        $this->attachFileToField($this->replacePlaceholders($file), 'Drop a file or click here');
        $this->getCurrentPage()->pressButton('Upload and import now');

        $this->getMainContext()->wait();
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) page$/
     */
    public function iAmOnTheImportJobPage(JobInstance $job)
    {
        $this->getNavigationContext()->openPage('Import show', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) edit page$/
     */
    public function iAmOnTheImportJobEditPage(JobInstance $job)
    {
        $this->getNavigationContext()->openPage('Import edit', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @When /^I launch the ("([^"]*)" (import|export) job)$/
     */
    public function iLaunchTheExportJob(JobInstance $job)
    {
        $jobType = ucfirst($job->getType());
        $this->getNavigationContext()->openPage(sprintf('%s launch', $jobType), ['id' => $job->getId()]);
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
}
