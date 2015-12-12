<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Step;

use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Component\Connector\Reader\File\CsvReader;
use Pim\Component\Connector\Writer\Doctrine\BaseWriter;
use PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization\TagProcessor;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;

class TagStepSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('aName');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\PimEnterprise\Component\ProductAsset\Connector\Step\TagStep');
    }

    function it_is_a_step()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepInterface');
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\AbstractStep');
    }

    function it_executes(
        StepExecution $stepExecution,
        BaseWriter $writer,
        TagProcessor $tagProcessor,
        CsvReader $csvReader,
        TagInterface $tag,
        JobRepositoryInterface $jobRepository
    ) {
        $value = [
            'code' => 'mycode',
            'localized'   => 0,
            'description' => 'My awesome description',
            'tags'        => 'dog,flowers,cities,animal,sunset',
            'end_of_use'  => '2018-02-01',
        ];

        $csvReader->read()->willReturn($value, null);

        $csvReader->setStepExecution($stepExecution)->shouldBeCalled();
        $csvReader->initialize()->shouldBeCalled();
        $csvReader->flush()->shouldBeCalled();

        $tagProcessor->setStepExecution($stepExecution)->shouldBeCalled();
        $tagProcessor->initialize()->shouldBeCalled();
        $tagProcessor->process($value)->willReturn([$tag], null);
        $tagProcessor->flush()->willReturn();

        $writer->setStepExecution($stepExecution)->shouldBeCalled();
        $writer->initialize()->shouldBeCalled();
        $writer->write([$tag])->shouldBeCalled();
        $writer->flush()->shouldBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->setReader($csvReader);
        $this->setProcessor($tagProcessor);
        $this->setWriter($writer);
        $this->setJobRepository($jobRepository);

        $this->doExecute($stepExecution);
    }
}
