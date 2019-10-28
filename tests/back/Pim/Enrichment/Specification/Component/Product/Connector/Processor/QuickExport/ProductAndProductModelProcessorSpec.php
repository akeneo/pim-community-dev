<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\QuickExport;

use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductModelValues;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductValues;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAndProductModelProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        FillMissingValuesInterface $fillMissingProductValues,
        ObjectDetacherInterface $detacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        BulkMediaFetcher $bulkMediaFetcher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $fillMissingProductModelValues,
            $fillMissingProductValues,
            $detacher,
            $userProvider,
            $tokenStorage,
            $bulkMediaFetcher
        );

        $this->setStepExecution($stepExecution);
    }

    function is_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelProcessorSpec::class);
    }

    function it_should_throw_exception_when_scope_is_not_present(
        $stepExecution,
        $userProvider,
        $tokenStorage,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('scope')->willReturn(false);

        $this->shouldThrow('\InvalidArgumentException')->duringProcess($product);
    }

    function it_process_product_with_selected_properties(
        $stepExecution,
        $userProvider,
        $tokenStorage,
        $channelRepository,
        $attributeRepository,
        $normalizer,
        $detacher,
        FillMissingValuesInterface $fillMissingProductValues,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

        $jobParameters->has('selected_locales')->willReturn(true);
        $jobParameters->get('selected_locales')->willReturn(['en_US']);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('scope')->willReturn(true);
        $jobParameters->get('scope')->willReturn('ecommerce');
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US']);

        $jobParameters->get('selected_properties')->willReturn(['identifier', 'family', 'description-en_US']);
        $attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER])->willReturn($attribute);
        $attribute->getCode()->willReturn('sku');

        $jobParameters->has('with_media')->willReturn(false);

        $detacher->detach($product)->shouldBeCalled();

        $standardFormat = [
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ];
        $normalizer->normalize($product, 'standard', Argument::any())->willReturn($standardFormat);
        $fillMissingProductValues->fromStandardFormat($standardFormat)->willReturn($standardFormat);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ]);
    }


    function it_process_product_without_selected_properties(
        $stepExecution,
        $userProvider,
        $tokenStorage,
        $channelRepository,
        $attributeRepository,
        $normalizer,
        $detacher,
        FillMissingValuesInterface $fillMissingProductValues,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

        $jobParameters->has('selected_locales')->willReturn(true);
        $jobParameters->get('selected_locales')->willReturn(['en_US']);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('scope')->willReturn(true);
        $jobParameters->get('scope')->willReturn('ecommerce');
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US']);

        $jobParameters->get('selected_properties')->willReturn(null);
        $attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER])->willReturn($attribute);
        $attribute->getCode()->willReturn('sku');

        $jobParameters->has('with_media')->willReturn(false);

        $detacher->detach($product)->shouldBeCalled();

        $standardFormat = [
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ];
        $normalizer->normalize($product, 'standard', Argument::any())->willReturn($standardFormat);
        $fillMissingProductValues->fromStandardFormat($standardFormat)->willReturn($standardFormat);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ]);
    }

    function it_process_product_model_with_selected_properties(
        $stepExecution,
        $userProvider,
        $tokenStorage,
        $channelRepository,
        $attributeRepository,
        $normalizer,
        $detacher,
        FillMissingValuesInterface $fillMissingProductModelValues,
        ProductModelInterface $productModel,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

        $jobParameters->has('selected_locales')->willReturn(true);
        $jobParameters->get('selected_locales')->willReturn(['en_US']);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('scope')->willReturn(true);
        $jobParameters->get('scope')->willReturn('ecommerce');
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US']);

        $jobParameters->get('selected_properties')->willReturn(['identifier', 'family', 'description-en_US']);
        $attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER])->willReturn($attribute);
        $attribute->getCode()->willReturn('sku');

        $jobParameters->has('with_media')->willReturn(false);

        $detacher->detach($productModel)->shouldBeCalled();

        $standardFormat = [
            'code' => 'foo',
            'family_variant' => 'shoes',
            'values' => []
        ];
        $normalizer->normalize($productModel, 'standard', Argument::any())->willReturn($standardFormat);
        $fillMissingProductModelValues->fromStandardFormat($standardFormat)->willReturn($standardFormat);

        $this->process($productModel)->shouldReturn([
            'code' => 'foo',
            'family_variant' => 'shoes',
            'values' => []
        ]);
    }

    function it_process_product_with_selected_properties_and_with_media(
        $stepExecution,
        $userProvider,
        $tokenStorage,
        $channelRepository,
        $attributeRepository,
        $normalizer,
        $detacher,
        $bulkMediaFetcher,
        FillMissingValuesInterface $fillMissingProductValues,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute,
        ExecutionContext $executionContext,
        WriteValueCollection $valueCollection,
        WriteValueCollection $filteredValues
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

        $jobParameters->has('selected_locales')->willReturn(true);
        $jobParameters->get('selected_locales')->willReturn(['en_US']);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('scope')->willReturn(true);
        $jobParameters->get('scope')->willReturn('ecommerce');
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US']);

        $jobParameters->get('selected_properties')->willReturn(['identifier', 'family', 'description-en_US']);
        $attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER])->willReturn($attribute);
        $attribute->getCode()->willReturn('sku');

        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp');
        $product->getIdentifier()->willReturn('sandal');
        $product->getValues()->willReturn($valueCollection);

        $valueCollection->filter(Argument::any())->willReturn($filteredValues);
        $bulkMediaFetcher->fetchAll($filteredValues, '/tmp', 'sandal')->shouldBeCalled();
        $bulkMediaFetcher->getErrors()->willReturn([]);

        $detacher->detach($product)->shouldBeCalled();

        $standardFormat = [
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ];

        $normalizer->normalize($product, 'standard', Argument::any())->willReturn($standardFormat);
        $fillMissingProductValues->fromStandardFormat($standardFormat)->willReturn($standardFormat);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ]);
    }

    function it_process_product_without_selected_properties_but_with_media(
        $stepExecution,
        $userProvider,
        $tokenStorage,
        $channelRepository,
        $attributeRepository,
        $normalizer,
        $detacher,
        $bulkMediaFetcher,
        FillMissingValuesInterface $fillMissingProductValues,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute,
        ExecutionContext $executionContext,
        WriteValueCollection $valueCollection
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

        $jobParameters->has('selected_locales')->willReturn(true);
        $jobParameters->get('selected_locales')->willReturn(['en_US']);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('scope')->willReturn(true);
        $jobParameters->get('scope')->willReturn('ecommerce');
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US']);

        $jobParameters->get('selected_properties')->willReturn(null);
        $attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER])->willReturn($attribute);
        $attribute->getCode()->willReturn('sku');

        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp');
        $product->getIdentifier()->willReturn('sandal');
        $product->getValues()->willReturn($valueCollection);

        $valueCollection->filter(Argument::any())->shouldNotBeCalled();
        $bulkMediaFetcher->fetchAll($valueCollection, '/tmp', 'sandal')->shouldBeCalled();
        $bulkMediaFetcher->getErrors()->willReturn([]);

        $detacher->detach($product)->shouldBeCalled();

        $standardFormat = [
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ];

        $normalizer->normalize($product, 'standard', Argument::any())->willReturn($standardFormat);
        $fillMissingProductValues->fromStandardFormat($standardFormat)->willReturn($standardFormat);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'values' => []
        ]);
    }
}
