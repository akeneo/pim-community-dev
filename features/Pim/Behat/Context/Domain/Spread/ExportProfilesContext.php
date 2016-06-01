<?php

namespace Pim\Behat\Context\Domain\Spread;

use Akeneo\Component\Batch\Model\JobInstance;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Pim\Behat\Context\PimContext;
use Symfony\Component\Yaml\Yaml;

class ExportProfilesContext extends PimContext
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
        $csvFile = $this->getCsvFile($path, $config);

        $expectedLines = $this->getExpectedLines($csv, $config);
        $actualLines = $this->getActualLines($csvFile, $config);

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
        $csvFile = $this->getCsvFile($path, $config);

        $expectedLines = $this->getExpectedLines($csv, $config);
        $actualLines = $this->getActualLines($csvFile, $config);

        $this->compareFileHeadersOrder($expectedLines[0], $actualLines[0]);
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
     * @Then /^export directory of "([^"]*)" should contain the following media:$/
     *
     * @throws ExpectationException
     */
    public function exportDirectoryOfShouldContainTheFollowingMedia($code, TableNode $table)
    {
        $config = $this->getFixturesContext()->getJobInstance($code)->getRawConfiguration();

        $path = dirname($config['filePath']);

        if (!is_dir($path)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Directory "%s" doesn\'t exist', $path)
            );
        }

        foreach ($table->getRows() as $data) {
            $file = rtrim($path, '/') . '/' .$data[0];

            if (!is_file($file)) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('File \"%s\" doesn\'t exist', $file)
                );
            }
        }
    }

    /**
     * @param array  $expectedLines
     * @param array  $actualLines
     * @param string $path
     *
     * @throws \Exception
     */
    protected function compareFile(array $expectedLines, array $actualLines, $path)
    {
        $expectedCount = count($expectedLines);
        $actualCount = count($actualLines);
        assertSame(
            $expectedCount,
            $actualCount,
            sprintf('Expecting to see %d rows, found %d', $expectedCount, $actualCount)
        );

        $headerDiff = array_diff($actualLines[0], $expectedLines[0]);
        if (0 !== count(array_diff($actualLines[0], $expectedLines[0]))) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match \n expected one: %s \n missing headers : %s",
                    $path,
                    implode(' | ', $actualLines[0]),
                    implode(' | ', $headerDiff)
                )
            );
        }

        unset($actualLines[0]);
        unset($expectedLines[0]);

        foreach ($actualLines as $actualLine) {
            $rows = array_filter($expectedLines, function ($item) use ($actualLine) {
                return 0 === count(array_diff($item, $actualLine));
            });


            if (1 !== count($rows)) {
                throw new \Exception(
                    sprintf('Could not find a line containing "%s" in %s', implode(' | ', $actualLine), $path)
                );
            }
        }
    }

    /**
     * @param PyStringNode $csv
     * @param array $config
     *
     * @return array
     */
    protected function getExpectedLines(PyStringNode $csv, $config)
    {
        $expectedLines = [];
        foreach ($csv->getLines() as $line) {
            if (!empty($line)) {
                $expectedLines[] = explode($config['delimiter'], str_replace($config['enclosure'], '', $line));
            }
        }

        return $expectedLines;
    }

    /**
     * @param \SplFileObject $csvFile
     * @param array $config
     *
     * @return array
     */
    protected function getActualLines(\SplFileObject $csvFile, array $config)
    {
        $actualLines = [];
        while ($data = $csvFile->fgetcsv()) {
            if (!empty($data)) {
                $actualLines[] = array_map(
                    function ($item) use ($config) {
                        return str_replace($config['enclosure'], '', $item);
                    },
                    $data
                );
            }
        }

        return $actualLines;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    protected function getCsvJobConfiguration($code)
    {
        $config = $this->getFixturesContext()->getJobInstance($code)->getRawConfiguration();
        $config['delimiter'] = isset($config['delimiter']) ? $config['delimiter'] : ';';
        $config['enclosure'] = isset($config['enclosure']) ? $config['enclosure'] : '"';
        $config['escape'] = isset($config['escape']) ? $config['escape'] : '\\';

        return $config;
    }

    /**
     * @param string $path
     * @param array $config
     *
     * @return \SplFileObject
     */
    protected function getCsvFile($path, array $config)
    {
        $csvFile = new \SplFileObject($path);
        $csvFile->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );
        $csvFile->setCsvControl($config['delimiter'], $config['enclosure'], $config['escape']);

        return $csvFile;
    }

    /**
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    protected function compareFileHeadersOrder(array $expectedHeaders, array $actualHeaders)
    {
        assertEquals(
            $expectedHeaders[0],
            $actualHeaders[0],
            sprintf('Expecting to see headers order like %d , found %d', $expectedHeaders[0], $actualHeaders[0])
        );
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
}
