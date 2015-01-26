<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

class ProductWriterSpec extends ObjectBehavior
{
    function let(MediaManager $mediaManager, StepExecution $stepExecution)
    {
        $this->beConstructedWith($mediaManager);
        $this->setStepExecution($stepExecution);
    }

    function it_writes_product_data_in_file_and_copy_medias($mediaManager, $stepExecution, ProductMediaInterface $mediaOne, ProductMediaInterface $mediaTwo)
    {
        $this->setFilePath('/tmp/tmp-file');

        $mediaManager->copy($mediaOne, '/tmp')->shouldBeCalled();
        $mediaManager->copy($mediaTwo, '/tmp')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('write')->shouldBeCalled();

        $this->write([['entry' => 'my-content', 'media' => [$mediaOne, $mediaTwo]]]);
    }
}
