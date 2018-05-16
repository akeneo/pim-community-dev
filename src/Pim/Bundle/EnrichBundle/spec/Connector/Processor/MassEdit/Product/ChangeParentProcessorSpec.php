<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeParentProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater
    )
    {
        $this->beConstructedWith($productValidator, $productModelValidator, $productUpdater, $productModelUpdater);
    }

    public function it_throws_an_exception_if_product_is_not_a_correct_type()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringProcess(new \stdClass());
    }

    public function it_changes_the_parent_of_a_variant_product(
        $productValidator,
        $productUpdater,
        EntityWithFamilyVariantInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    )
    {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $productUpdater->update($product, ['parent' => '42'])->shouldBeCalled();

        $this->process($product)->shouldReturn($product);
    }

    public function it_fails_to_update_an_invalid_product(
        $productValidator,
        $productUpdater,
        EntityWithFamilyVariantInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    )
    {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $violation = new ConstraintViolation('error1', '', [], '', '', '');
        $violations = new ConstraintViolationList([$violation]);
        $productValidator->validate($product)->willReturn($violations);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $productUpdater->update($product, ['parent' => '42'])->shouldBeCalled();

        $this->process($product)->shouldReturn(null);
    }
}
