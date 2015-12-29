<?php

namespace Pim\Behat\Context\Domain\Spread;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\PimContext;

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

        $expectedCount = count($expectedLines);
        $actualCount   = count($actualLines);
        assertSame(
            $expectedCount,
            $actualCount,
            sprintf('Expecting to see %d rows, found %d', $expectedCount, $actualCount)
        );

        if (md5(json_encode($actualLines[0])) !== md5(json_encode($expectedLines[0]))) {
            throw new \Exception(
                sprintf(
                    'Header in the file %s does not match expected one: %s',
                    $path,
                    implode(' | ', $actualLines[0])
                )
            );
        }
        unset($actualLines[0]);
        unset($expectedLines[0]);

        foreach ($expectedLines as $expectedLine) {
            $originalExpectedLine = $expectedLine;
            $found = false;
            foreach ($actualLines as $index => $actualLine) {
                // Order of columns is not ensured
                // Sorting the line values allows to have two identical lines
                // with values in different orders
                sort($expectedLine);
                sort($actualLine);

                // Same thing for the rows
                // Order of the rows is not reliable
                // So we generate a hash for the current line and ensured that
                // the generated file contains a line with the same hash
                if (md5(json_encode($actualLine)) === md5(json_encode($expectedLine))) {
                    $found = true;

                    // Unset line to prevent comparing it twice
                    unset($actualLines[$index]);

                    break;
                }
            }
            if (!$found) {
                throw new \Exception(
                    sprintf(
                        'Could not find a line containing "%s" in %s',
                        implode(' | ', $originalExpectedLine),
                        $path
                    )
                );
            }
        }
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
     * @Then /^the path of the exported file of "([^"]+)" should be "([^"]+)"$/
     */
    public function thePathOfTheExportedFileOfShouldBe($code, $path)
    {
        $executionPath = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code);

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
}
