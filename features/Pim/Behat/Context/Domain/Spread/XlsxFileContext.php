<?php

namespace Pim\Behat\Context\Domain\Spread;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class XlsxFileContext extends PimContext
{
    /**
     * @param int|null  $number
     * @param string    $code
     * @param TableNode $expectedLines
     *
     * @Then /^exported xlsx file( \d+)? of "([^"]*)" should contain:$/
     */
    public function exportedXlsxFileOfShouldContain($number = null, $code, TableNode $expectedLines)
    {
        $number = '' === $number ? null : trim($number);
        $path = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code, $number);

        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($path);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $actualLines = iterator_to_array($sheet->getRowIterator());
        $reader->close();

        $this->compareFile(array_values($expectedLines->getRows()), array_values($actualLines), $path);
    }

    /**
     * @param string    $code
     * @param TableNode $expectedLines
     *
     * @Then /^exported xlsx files of "([^"]*)" should contain:$/
     */
    public function exportedXlsxFilesOfShouldContain($code, TableNode $expectedLines)
    {
        $filePaths = $this->getMainContext()->getSubcontext('job')->getAllJobInstancePaths($code);

        $expectedLines = $expectedLines->getRows();
        unset($expectedLines[0]);

        $reader = ReaderFactory::create(Type::XLSX);

        foreach ($filePaths as $path) {
            $reader->open($path);
            $sheet = current(iterator_to_array($reader->getSheetIterator()));
            $actualLines = iterator_to_array($sheet->getRowIterator());

            foreach ($actualLines as $actualLine) {
                $expectedLines = array_filter($expectedLines, function ($expected) use ($actualLine) {
                    return count(array_diff($expected, $actualLine)) > 0;
                });
            }

            $reader->close();
        }

        $lines = false === current($expectedLines) ? [] : current($expectedLines);

        Assert::assertEmpty(
            $expectedLines,
            sprintf('Could not find an expected line: %s', implode(' | ', $lines))
        );
    }

    /**
     * @param string    $code
     * @param TableNode $expectedLines
     *
     * @Then /^exported xlsx file of "([^"]*)" should contains the following headers:$/
     */
    public function exportedXlsxFileOfShouldContainsTheFollowingHeaders($code, TableNode $expectedLines)
    {
        $path = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code);

        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($path);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $actualLines = iterator_to_array($sheet->getRowIterator());
        $reader->close();

        $this->compareXlsxFileHeadersOrder(array_values($expectedLines->getRows()), array_values($actualLines));
    }

    /**
     * @param string $fileName
     * @param int    $rows
     *
     * @Given /^xlsx file "([^"]*)" should contain (\d+) rows$/
     *
     * @throws ExpectationException
     */
    public function xlsxFileShouldContainRows($fileName, $rows)
    {
        $fileName = $this->replacePlaceholders($fileName);
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($fileName);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $actualLines = iterator_to_array($sheet->getRowIterator());
        $reader->close();

        $rowCount = count($actualLines);

        Assert::assertEquals($rows, $rowCount, sprintf('Expecting file to contain %d rows, found %d.', $rows, $rowCount));
    }

    /**
     * @param int    $number
     * @param string $code
     * @param int    $itemsCount
     *
     * @Then /^exported xlsx file (\d+) of "([^"]*)" should contain (\d+) rows?$/
     */
    public function exportedXlsxFileOfShouldContainItems($number, $code, $itemsCount)
    {
        $path = $this->getMainContext()->getSubcontext('job')->getJobInstancePath($code, $number);
        $this->xlsxFileShouldContainRows($path, $itemsCount);
    }

    /**
     * @Given /^the category order in the xlsx file "([^"]*)" should be following:$/
     */
    public function theCategoryOrderInTheXlsxFileShouldBeFollowing($fileName, TableNode $table)
    {
        $fileName = $this->replacePlaceholders($fileName);
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $categories = [];
        foreach (array_keys($table->getRowsHash()) as $category) {
            $categories[] = $category;
        }

        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($fileName);

        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        $actualLines = iterator_to_array($sheet->getRowIterator());
        array_shift($actualLines);
        $reader->close();

        foreach ($actualLines as $row) {
            $category = array_shift($categories);
            Assert::assertSame($category, $row[0], sprintf('Expecting category "%s", saw "%s"', $category, $row[0]));
        }
    }

    /**
     * @param array  $expectedLines
     * @param array  $actualLines
     * @param string $path
     *
     * @throws UnsupportedTypeException
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
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    protected function compareXlsxFileHeadersOrder(array $expectedHeaders, array $actualHeaders)
    {
        Assert::assertEquals(
            $expectedHeaders[0],
            $actualHeaders[0],
            sprintf('Expecting to see headers order like %d , found %d', $expectedHeaders[0], $actualHeaders[0])
        );
    }
}
