<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Job\AttributeGroupCompleteness;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Job\AttributeGroupCompleteness\AttributeGroupCompletenessTasklet;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;
use Prophecy\Argument;

class AttributeGroupCompletenessTaskletSpec extends ObjectBehavior
{
    function let(
        ProjectItemCalculatorInterface $attributeGroupCompletenessCalculator,
        PreProcessingRepositoryInterface $preProcessingRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $attributeGroupCompletenessCalculator,
            $preProcessingRepository,
            $channelRepository,
            $localeRepository,
            $productRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupCompletenessTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_has_a_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_process_the_attribute_group_complete(
        $attributeGroupCompletenessCalculator,
        $preProcessingRepository,
        $localeRepository,
        $channelRepository,
        $productRepository,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);

        $jobParameters->get('product_identifier')->willReturn(40);
        $jobParameters->get('channel_identifier')->willReturn('ecommerce');
        $jobParameters->get('locale_identifier')->willReturn('en_US');

        $productRepository->find(40)->willReturn($product);
        $preProcessingRepository->belongsToAProject($product)->willreturn(true);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);

        $attributeGroupCompletenessCalculator->calculate($product, $channel, $locale)->willReturn([
            'attribute group completeness'
        ]);

        $preProcessingRepository->addAttributeGroupCompleteness($product, $channel, $locale, [
            'attribute group completeness'
        ])->shouldBeCalled();

        $this->execute()->shouldReturn(null);
    }

    function it_does_not_process_the_attribute_group_completeness(
        $attributeGroupCompletenessCalculator,
        $preProcessingRepository,
        $productRepository,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductInterface $product
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('product_identifier')->willReturn(40);

        $productRepository->find(40)->willReturn($product);
        $preProcessingRepository->belongsToAProject($product)->willreturn(false);

        $attributeGroupCompletenessCalculator->calculate(Argument::cetera())->shouldNotBeCalled();
        $preProcessingRepository->addAttributeGroupCompleteness(Argument::cetera())->shouldNotBeCalled();

        $this->execute()->shouldReturn(null);
    }
}
