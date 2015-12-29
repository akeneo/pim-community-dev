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
}
