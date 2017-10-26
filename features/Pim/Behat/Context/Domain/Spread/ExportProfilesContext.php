<?php

namespace Pim\Behat\Context\Domain\Spread;

use Behat\ChainedStepsExtension\Step\Then;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\Domain\ImportExportContext;
use Symfony\Component\Yaml\Yaml;

class ExportProfilesContext extends ImportExportContext
{
    use SpinCapableTrait;

    /**
     * @param string       $number
     * @param string       $code
     * @param PyStringNode $csv
     *
     * @Then /^(first |second )?exported file of "([^"]*)" should contain:$/
     *
     * @throws ExpectationException
     * @throws \Exception
     */
    public function exportedFileOfShouldContain($number, $code, PyStringNode $csv)
    {
        $intNumber = null;
        if ('' !== $number) {
            $intNumber = 'first ' === $number ? 1 : 2;
        }

        $lines = $this->spin(function () use ($code, $csv, $intNumber) {
            $path = $this->getExportedFile($code, $intNumber);

            $config = $this->getCsvJobConfiguration($code);

            return [
                'expectedLines' => $this->getExpectedLines($csv, $config),
                'actualLines' => $this->getActualLines($path, 'csv', $config),
                'path' => $path
            ];
        }, sprintf('Can not find lines of the file %s', $code));

        $this->compareFile($lines['expectedLines'], $lines['actualLines'], $lines['path']);
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
        $this->spin(function () use ($code) {
            $path = $this->getExportedFile($code);
            $content = trim(file_get_contents($path));
            assertEmpty($content);

            return true;
        }, sprintf('Cannot validate that job %s is empty', $code));
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
        $this->spin(function () use ($code, $csv) {
            $path = $this->getExportedFile($code);
            $config = $this->getCsvJobConfiguration($code);

            $expectedLines = $this->getExpectedLines($csv, $config);
            $actualLines = $this->getActualLines($path, 'csv', $config);

            $this->compareFileHeadersOrder(current($expectedLines), current($actualLines));

            return true;
        }, sprintf('Cannot validate the header of %s', $code));
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
        $this->spin(function () use ($code, $yaml) {
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

            return true;
        }, sprintf('Cannot validate the yml file %s', $code));
    }

    /**
     * @When /^I launch the ("([^"]*)" import job)$/
     *
     * @return Then
     */
    public function iLaunchTheImportJob()
    {
        $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.AknCenteredBox .AknButton--apply');
        }, 'Cannot find the import button')->click();

        return new Then('I should see the text "Execution details"');
    }

    /**
     * @When /^I launch the ("([^"]*)" export job)$/
     *
     * @return Then
     */
    public function iLaunchTheExportJob()
    {
        $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.AknTitleContainer-meta .AknButton--apply');
        }, 'Cannot find the export button')->click();

        return new Then('I should see the text "Execution details"');
    }

    /**
     * @param string $code
     * @param string $path
     *
     * @Then /^the name of the exported file of "([^"]+)" should be "([^"]+)"$/
     *
     * @throws ExpectationException
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
     * @param string $code
     * @param string $paths
     *
     * @Then /^the names of the exported files of "([^"]+)" should be "([^"]+)"$/
     *
     * @throws ExpectationException
     */
    public function theNamesOfTheExportedFilesOfShouldBe($code, $paths)
    {
        $executionPaths = $this->getMainContext()->getSubcontext('job')->getJobInstanceFilenames($code);
        $expectedPaths = explode(',', $paths);
        sort($executionPaths);
        sort($expectedPaths);

        if ($executionPaths !== $expectedPaths) {
            throw $this->getMainContext()->createExpectationException(sprintf(
                'Expected file names "%s" got "%s"',
                join(',', $expectedPaths),
                join(',', $executionPaths)
            ));
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
        $path = dirname($jobInstance->getRawParameters()['filePath']);

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
        $path = dirname($jobInstance->getRawParameters()['filePath']);

        $this->checkExportDirectoryFiles(false, $table, $path);
    }

    /**
     * Check if files should be in the export directory of the job with the given $code
     *
     * @param bool      $shouldBeInDirectory true if the files should be in the directory, false otherwise
     * @param TableNode $table               Files to check
     * @param string    $path                Path of item on filesystem
     *
     * @throws ExpectationException
     */
    protected function checkExportDirectoryFiles($shouldBeInDirectory, TableNode $table, $path)
    {
        if ($shouldBeInDirectory && !is_dir($path)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Directory "%s" doesn\'t exist', $path)
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
     * @param string       $code
     * @param integer|null $number
     *
     * @throws ExpectationException
     * @return string
     *
     */
    protected function getExportedFile($code, $number = null)
    {
        $filePath = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code, $number);
        if (!is_file($filePath)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('File "%s" doesn\'t exist', $filePath)
            );
        }

        return $filePath;
    }
}
