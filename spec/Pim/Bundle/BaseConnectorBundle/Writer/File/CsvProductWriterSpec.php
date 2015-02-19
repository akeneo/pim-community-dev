<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;

class CsvProductWriterSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    function it_writes_product_data_in_file_and_copy_medias($stepExecution)
    {
        $file = new \SplFileInfo(realpath(__DIR__.'/../../../../../../features/Context/fixtures/product_export_with_non_utf8_characters.csv'));
        $media = ['filePath' => $file->getPathname(), 'exportPath' => 'test.csv'];

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn(['/tmp/test.csv' => 'test.csv']);
        $stepExecution->addWarning()->shouldNotBeCalled();
    }

    function it_does_not_copy_not_found_media($stepExecution)
    {
        $media = ['filePath' => 'not-found.csv', 'exportPath' => 'test.csv'];

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been found or is not currently available', [], $media)
            ->shouldBeCalled();
    }

    function it_does_not_copy_with_wrong_directory($stepExecution)
    {
        $file = new \SplFileInfo(realpath(__DIR__.'/../../../../../../features/Context/fixtures/product_export_with_non_utf8_characters.csv'));
        $media = ['filePath' => $file->getPathname(), 'exportPath' => null];

        $previousReporting = error_reporting();
        error_reporting(0);

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been copied', [], $media)
            ->shouldBeCalled();
        error_reporting($previousReporting);
    }
}
