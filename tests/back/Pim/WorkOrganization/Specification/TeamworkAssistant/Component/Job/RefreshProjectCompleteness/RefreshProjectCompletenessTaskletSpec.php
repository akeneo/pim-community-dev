<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\RefreshProjectCompleteness;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\RefreshProjectCompleteness\RefreshProjectCompletenessTasklet;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;
use Prophecy\Argument;

class RefreshProjectCompletenessTaskletSpec extends ObjectBehavior
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
        $this->shouldHaveType(RefreshProjectCompletenessTasklet::class);
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
