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
        $config = $this->getFixturesContext()->getJobInstance($code)->getRawConfiguration();

        $path = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code);

        if (!is_file($path)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('File "%s" doesn\'t exist', $path)
            );
        }

        $delimiter = isset($config['delimiter']) ? $config['delimiter'] : ';';
        $enclosure = isset($config['enclosure']) ? $config['enclosure'] : '"';
        $escape    = isset($config['escape'])    ? $config['escape']    : '\\';

        $csvFile = new \SplFileObject($path);
        $csvFile->setFlags(
            \SplFileObject::READ_CSV   |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );
        $csvFile->setCsvControl($delimiter, $enclosure, $escape);

        $expectedLines = [];
        foreach ($csv->getLines() as $line) {
            if (!empty($line)) {
                $expectedLines[] = explode($delimiter, str_replace($enclosure, '', $line));
            }
        }

        $actualLines = [];
        while ($data = $csvFile->fgetcsv()) {
            if (!empty($data)) {
                $actualLines[] = array_map(
                    function ($item) use ($enclosure) {
                        return str_replace($enclosure, '', $item);
                    },
                    $data
                );
            }
        }
        $this->compareFile($expectedLines, $actualLines, $path);
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
        $path = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code);

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
     * @param string    $code
     * @param TableNode $exectedLines
     *
     * @Then /^exported xlsx file of "([^"]*)" should contain:$/
     *
     * @throws ExpectationException
     * @throws \Exception
     */
    public function exportedXlsxFileOfShouldContain($code, TableNode $expectedLines)
    {
        $path = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code);

        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($path);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $actualLines = iterator_to_array($sheet->getRowIterator());
        $reader->close();

        $this->compareFile(array_values($expectedLines->getRows()), array_values($actualLines), $path);
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

        if (0 !== count(array_diff($actualLines[0], $expectedLines[0]))) {
            throw new \Exception(
                sprintf('Header in the file %s does not match expected one: %s', $path, implode(' | ', $actualLines[0]))
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
}
