<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\VariantGroupCleaner;
use Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductReader;
use Pim\Component\Catalog\Model\ProductInterface;

class FilteredVariantGroupProductReaderSpec extends ObjectBehavior
{
    function let(FilteredProductReader $reader, VariantGroupCleaner $cleaner, StepExecution $stepExecution)
    {
        $this->beConstructedWith($reader, $cleaner);
        $this->setStepExecution($stepExecution);
    }

    function it_reads_products(
        $reader,
        $cleaner,
        StepExecution $stepExecution,
        ProductInterface $product
    ) {
        $configuration = [
            'filters' => [
                'field'    => 'id',
                'operator' => 'IN',
                'value'    => [12, 13, 14]
            ],
            'actions' => []
        ];
        $this->setConfiguration($configuration);
        $cleanedConfiguration = [
            'filters' => [
                'field'    => 'id',
                'operator' => 'IN',
                'value'    => [12, 13]
            ],
            'actions' => []
        ];
        $cleaner->clean($configuration, $stepExecution)->willReturn($cleanedConfiguration);
        $reader->setStepExecution($stepExecution)->shouldBeCalled();
        $reader->setConfiguration($cleanedConfiguration)->shouldBeCalled();
        $reader->read()->willReturn($product);
        $this->read()->shouldReturn($product);
    }
}
