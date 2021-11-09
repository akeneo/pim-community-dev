<?php

namespace Pim\Behat\Context\Domain;

use Behat\Gherkin\Node\PyStringNode;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader as CsvReader;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class ImportExportContext extends PimContext
{
    /**
     * @throws \Exception
     */
    protected function compareFile(array $expectedLines, array $actualLines, string $path)
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
                    implode(' | ', $currentActualLines)
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
     * @throws \Exception
     */
    public function compareLines(array $expectedLines, array $actualLines, string $path)
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
                    implode(' | ', $currentActualLines)
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

    protected function getExpectedLines(PyStringNode $behatData, array $config): array
    {
        $delimiter = $config['delimiter'] ?? ';';
        $enclosure = $config['enclosure'] ?? '';

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

        switch ($fileType) {
            case Type::XLSX:
                $reader = ReaderEntityFactory::createXLSXReader();
                break;
            case Type::CSV:
                $reader = ReaderEntityFactory::createCSVReader();
                break;
            case Type::ODS:
                $reader = ReaderEntityFactory::createODSReader();
                break;
            default:
                throw new \LogicException('Invalid type for FlatIterator reader');
        }

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

    protected function getCsvJobConfiguration(string $code): array
    {
        $config = $this->getFixturesContext()->getJobInstance($code)->getRawParameters();
        $config['delimiter'] = $config['delimiter'] ?? ';';
        $config['enclosure'] = $config['enclosure'] ?? '"';
        $config['escape'] = $config['escape'] ?? '\\';

        return $config;
    }

    protected function getXlsxJobConfiguration(string $code): array
    {
        return $this->getFixturesContext()->getJobInstance($code)->getRawParameters();
    }

    protected function compareFileHeadersOrder(array $expectedHeaders, array $actualHeaders)
    {
        Assert::assertEquals(
            $expectedHeaders,
            $actualHeaders,
            sprintf('Expecting to see headers order like %d , found %d', $expectedHeaders, $actualHeaders)
        );
    }
}
