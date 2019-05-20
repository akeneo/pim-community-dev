<?php

namespace Pim\Behat\Context\Domain;

use Behat\Gherkin\Node\PyStringNode;
use Box\Spout\Common\Type;
use Box\Spout\Reader\CSV\Reader as CsvReader;
use Box\Spout\Reader\ReaderFactory;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class ImportExportContext extends PimContext
{
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
        Assert::assertSame(
            $expectedCount,
            $actualCount,
            sprintf('Expecting to see %d rows, found %d', $expectedCount, $actualCount)
        );

        $currentActualLines = current($actualLines);
        $currentExpectedLines = current($expectedLines);

        $headerDiff = array_diff($currentActualLines, $currentExpectedLines);
        if (0 !== count($headerDiff)) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match the expected one. Given:\n\t%s\nNon expected headers: %s",
                    $path,
                    implode(' | ', $currentActualLines),
                    implode(' | ', $headerDiff)
                )
            );
        }

        $headerDiff = array_diff($currentExpectedLines, $currentActualLines);
        if (0 !== count($headerDiff)) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match the expected one. Given:\n\t%s\nMissing headers: %s",
                    $path,
                    implode(' | ', $currentActualLines),
                    implode(' | ', $headerDiff)
                )
            );
        }

        if (count($currentExpectedLines) !== count($currentActualLines)) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match the expected one. Given:\n\t%s\nDuplicated fields detected.",
                    $path,
                    implode(' | ', $currentActualLines),
                    implode(' | ', $headerDiff)
                )
            );
        }

        array_shift($actualLines);
        array_shift($expectedLines);

        foreach ($expectedLines as $expectedLine) {
            $rows = array_filter($actualLines, function ($actualLine) use ($expectedLine) {
                return 0 === count(array_diff($expectedLine, $actualLine));
            });

            if (1 !== count($rows)) {
                throw new \Exception(
                    sprintf('Could not find a line containing "%s" in %s', implode(' | ', $expectedLine), $path)
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
    public function compareLines(array $expectedLines, array $actualLines, $path)
    {
        $currentActualLines = current($actualLines);
        $currentExpectedLines = current($expectedLines);

        $headerDiff = array_diff($currentActualLines, $currentExpectedLines);
        if (0 !== count($headerDiff)) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match the expected one. Given:\n\t%s\nNon expected headers: %s",
                    $path,
                    implode(' | ', $currentActualLines),
                    implode(' | ', $headerDiff)
                )
            );
        }

        $headerDiff = array_diff($currentExpectedLines, $currentActualLines);
        if (0 !== count($headerDiff)) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match the expected one. Given:\n\t%s\nMissing headers: %s",
                    $path,
                    implode(' | ', $currentActualLines),
                    implode(' | ', $headerDiff)
                )
            );
        }

        if (count($currentExpectedLines) !== count($currentActualLines)) {
            throw new \Exception(
                sprintf(
                    "Header in the file %s does not match the expected one. Given:\n\t%s\nDuplicated fields detected.",
                    $path,
                    implode(' | ', $currentActualLines),
                    implode(' | ', $headerDiff)
                )
            );
        }

        array_shift($actualLines);
        array_shift($expectedLines);

        foreach ($expectedLines as $expectedLine) {
            if (false === $this->lineExists($expectedLine, $actualLines)) {
                throw new \Exception(
                    sprintf('Could not find a line containing "%s" in %s', implode(' | ', $expectedLine), $path)
                );
            }
        }
    }

    /**
     * Test if a line exists in an array of lines
     *
     * @param $searchedLine
     * @param $actualLines
     * @return bool
     */
    private function lineExists($searchedLine, $actualLines): bool
    {
        foreach ($actualLines as $actualLine) {
            if (count($actualLine) === count($searchedLine) && $actualLine == $searchedLine) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PyStringNode $behatData
     * @param array        $config
     *
     * @return array
     */
    protected function getExpectedLines(PyStringNode $behatData, $config)
    {
        $delimiter = isset($config['delimiter']) ? $config['delimiter'] : ';';
        $enclosure = isset($config['enclosure']) ? $config['enclosure'] : '';

        $expectedLines = [];
        foreach ($behatData->getStrings() as $line) {
            if (!empty($line)) {
                $expectedLines[] = explode($delimiter, str_replace($enclosure, '', $line));
            }
        }

        return $expectedLines;
    }

    protected function getActualLinesFromArchive(string $archivePath, string $fileType, array $config): array
    {
        $jobContext = $this->getMainContext()->getSubcontext('job');

        $reader = ReaderFactory::create($fileType);

        if (Type::CSV === $fileType && $reader instanceof CsvReader) {
            $reader
                ->setFieldDelimiter($config['delimiter'])
                ->setFieldEnclosure($config['enclosure'])
            ;
        }

        $reader->open($jobContext->copyArchiveLocally($archivePath));
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $lines = iterator_to_array($sheet->getRowIterator());
        $reader->close();

        return $lines;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    protected function getCsvJobConfiguration($code)
    {
        $config = $this->getFixturesContext()->getJobInstance($code)->getRawParameters();
        $config['delimiter'] = isset($config['delimiter']) ? $config['delimiter'] : ';';
        $config['enclosure'] = isset($config['enclosure']) ? $config['enclosure'] : '"';
        $config['escape'] = isset($config['escape']) ? $config['escape'] : '\\';

        return $config;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    protected function getXlsxJobConfiguration($code)
    {
        return $this->getFixturesContext()->getJobInstance($code)->getRawParameters();
    }

    /**
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    protected function compareFileHeadersOrder(array $expectedHeaders, array $actualHeaders)
    {
        Assert::assertEquals(
            $expectedHeaders,
            $actualHeaders,
            sprintf('Expecting to see headers order like %d , found %d', $expectedHeaders, $actualHeaders)
        );
    }
}
