<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\Writer;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\DummyConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Writer\File\Yaml\Writer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlWriterIntegration extends KernelTestCase
{
    /** @var Writer */
    protected $writer;

    /** @var string */
    protected $filePath;

    /** @var string */
    protected $header;

    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->filePath = static::$kernel->getRootDir().'/../var/a_dump.yml';
        $this->header = 'a_header';

        $jobParameters = new JobParameters(['filePath' => $this->filePath]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('a_step', $jobExecution);
        $this->writer = new Writer(new DummyConverter(new FieldsRequirementChecker()), $this->header);
        $this->writer->setStepExecution($stepExecution);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unlink($this->filePath);
    }

    public function testItOverwriteTheFileWhenItsFlushed() {
        $items = $this->itemProviders();
        $this->writer->write($items);
        $this->writer->flush();
        $this->writer->write($items);

        $writtenItems = Yaml::parseFile($this->filePath);
        $this->assertEquals(count($items), count($writtenItems[$this->header]));
    }

    public function testItAppendsWithoutCopyingTheHeaderWhenItsNotFlushed() {
        $items = $this->itemProviders();
        $secondItems = $this->secondItemsProviders();
        $this->writer->write($items);
        $this->writer->write($secondItems);

        //parse file assert that the key is not copied otherwise it would have overwrite a key
        $writtenItems = Yaml::parseFile($this->filePath);
        $this->assertEquals(count($items) + count($secondItems), count($writtenItems[$this->header]));
    }

    private function itemProviders(): array
    {
        $letters = range('A', 'Z');
        $items = [];

        foreach ($letters as $letter) {
            $items[$letter] = [$letter => $letter];
        }

        return $items;
    }

    private function secondItemsProviders(): array
    {
        $letters = range('A', 'Z');
        $items = [];

        foreach ($letters as $letter) {
            $doubledLetter = $letter.$letter;
            $items[$doubledLetter] = [$doubledLetter => $doubledLetter];
        }

        return $items;
    }
}
