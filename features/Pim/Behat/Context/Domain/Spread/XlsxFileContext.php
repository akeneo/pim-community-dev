<?php

namespace Pim\Behat\Context\Domain\Spread;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Pim\Behat\Context\PimContext;

class XlsxFileContext extends PimContext
{
    /**
     * @param string    $code
     * @param TableNode $expectedLines
     *
     * @Then /^exported xlsx file of "([^"]*)" should contain:$/
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

        assertEquals($rows, $rowCount, sprintf('Expecting file to contain %d rows, found %d.', $rows, $rowCount));
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
            assertSame($category, $row[0], sprintf('Expecting category "%s", saw "%s"', $category, $row[0]));
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
