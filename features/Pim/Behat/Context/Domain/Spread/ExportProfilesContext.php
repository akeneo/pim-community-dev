<?php

namespace Pim\Behat\Context\Domain\Spread;

use Akeneo\Component\Batch\Model\JobInstance;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\Domain\ImportExportContext;
use Symfony\Component\Yaml\Yaml;

class ExportProfilesContext extends ImportExportContext
{
    /**
     * @param string       $code
     * @param PyStringNode $csv
     *
     * @Then /^exported file of "([^"]*)" should contain:$/
     *
     * @throws ExpectationException
     * @throws \Exception
     */
    public function exportedFileOfShouldContain($code, PyStringNode $csv)
    {
        $path = $this->getExportedFile($code);
        $config =  $this->getCsvJobConfiguration($code);

        $expectedLines = $this->getExpectedLines($csv, $config);
        $actualLines = $this->getActualLines($path, 'csv', $config);

        $this->compareFile($expectedLines, $actualLines, $path);
    }

    /**
     * @param string $code
     *
     * @Then /^exported file of "([^"]*)" should be empty$/
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function exportedFileOfShouldBeEmpty($code)
    {
        $path = $this->getExportedFile($code);
        $content = trim(file_get_contents($path));

        assertEmpty($content);
    }

    /**
     * @param string       $code
     * @param PyStringNode $csv
     *
     * @Then /^exported file of "([^"]*)" should contains the following headers:$/
     *
     * @throws ExpectationException
     * @throws \Exception
     */
    public function exportedFileOfShouldContainsTheFollowingHeaders($code, PyStringNode $csv)
    {
        $path = $this->getExportedFile($code);
        $config = $this->getCsvJobConfiguration($code);

        $expectedLines = $this->getExpectedLines($csv, $config);
        $actualLines = $this->getActualLines($path, 'csv', $config);

        $this->compareFileHeadersOrder(current($expectedLines), current($actualLines));
    }

    /**
     * @param string       $code
     * @param PyStringNode $yaml
     *
     * @Then /^exported yaml file of "([^"]*)" should contain:$/
     *
     * @throws \Exception
     */
    public function exportedYamlFileOfShouldContain($code, PyStringNode $yaml)
    {
        $path = $this->getExportedFile($code);

        $actualLines = Yaml::parse(file_get_contents($path));
        $expectedLines = Yaml::parse($yaml->getRaw());

        $isValidYamlFile = function ($expectedLines, $actualLines) use (&$isValidYamlFile) {
            foreach ($expectedLines as $key => $line) {
                $actualLine = $actualLines[$key];
                if (is_array($line)) {
                    $isValidYamlFile($line, $actualLine);
                }

                if ($line !== $actualLine) {
                    throw new \Exception(
                        sprintf('The exported file is not well formatted, expected %s, given %s', $line, $actualLine)
                    );
                }
            }
        };

        $isValidYamlFile($expectedLines, $actualLines);
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
     * @param string $code
     * @param string $path
     *
     * @Then /^the name of the exported file of "([^"]+)" should be "([^"]+)"$/
     */
    public function theNameOfTheExportedFileOfShouldBe($code, $path)
    {
        $executionPath = $this->getMainContext()->getSubcontext('job')->getJobInstanceFilename($code);

        if ($path !== $executionPath) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Expected file name "%s" got "%s"', $path, $executionPath)
            );
        }
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^export directory of "([^"]*)" should contain the following file:$/
     *
     * @throws ExpectationException
     */
    public function exportDirectoryOfShouldContainTheFollowingFile($code, TableNode $table)
    {
        $jobInstance = $this->getFixturesContext()->getJobInstance($code);
        $path = dirname($jobInstance->getRawParameters()['filePath']);

        $this->checkExportDirectoryFiles(true, $table, $path);
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^export directory of "([^"]*)" should contain the following media:$/
     *
     * @throws ExpectationException
     */
    public function exportDirectoryOfShouldContainTheFollowingMedia($code, TableNode $table)
    {
        $jobInstance = $this->getFixturesContext()->getJobInstance($code);
        $path = $this->getMediaWorkingDirectory($jobInstance, $code, $jobInstance->getRawParameters()['filePath']);

        $this->checkExportDirectoryFiles(true, $table, $path);
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^export directory of "([^"]*)" should not contain the following media:$/
     *
     * @throws ExpectationException
     */
    public function exportDirectoryOfShouldNotContainTheFollowingMedia($code, TableNode $table)
    {
        $jobInstance = $this->getFixturesContext()->getJobInstance($code);
        $path = $this->getMediaWorkingDirectory($jobInstance, $code, $jobInstance->getRawParameters()['filePath']);

        $this->checkExportDirectoryFiles(false, $table, $path);
    }

    /**
     * Check if files should be in the export directory of the job with the given $code
     *
     * @param bool      $shouldBeInDirectory true if the files should be in the directory, false otherwise
     * @param TableNode $table               Files to check
     * @param string    $path                Path of item on filesystem
     */
    protected function checkExportDirectoryFiles($shouldBeInDirectory, TableNode $table, $path)
    {
        if ($shouldBeInDirectory && !is_dir($path)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Directory "%s" doesn\'t exist', $path)
            );
        }

        if (!$shouldBeInDirectory && is_dir($path)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Directory "%s" exists, but it should not', $path)
            );
        }

        foreach ($table->getRows() as $data) {
            $file = rtrim($path, '/') . '/' .$data[0];

            if (!is_file($file) && $shouldBeInDirectory) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('File \"%s\" doesn\'t exist', $file)
                );
            }

            if (is_file($file) && !$shouldBeInDirectory) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('File \"%s\" exists, but it should not', $file)
                );
            }
        }
    }

    /**
     * @param string $code
     *
     * @throws ExpectationException
     * @return string
     *
     */
    protected function getExportedFile($code)
    {
        $filePath = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code);
        if (!is_file($filePath)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('File "%s" doesn\'t exist', $filePath)
            );
        }

        return $filePath;
    }

    /**
     * Build path of the working directory to import media in a specific directory.
     * Will be extracted with TIP-539
     *
     * @param JobInstance $jobInstance
     * @param string      $code
     * @param string      $filePath
     *
     * @return string
     */
    protected function getMediaWorkingDirectory(JobInstance $jobInstance, $code, $filePath)
    {
        return dirname($filePath)
               . DIRECTORY_SEPARATOR
               . $code
               . DIRECTORY_SEPARATOR
               . $jobInstance->getJobExecutions()->first()->getId()
               . DIRECTORY_SEPARATOR;
    }
}
