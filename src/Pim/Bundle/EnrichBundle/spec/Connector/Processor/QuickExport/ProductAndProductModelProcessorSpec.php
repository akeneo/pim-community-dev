<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
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
        EntityWithFamilyValuesFillerInterface $valuesFiller,
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
            $valuesFiller,
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

        $normalizer->normalize($product, 'standard', Argument::any())->willReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
        ]);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes'
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

        $normalizer->normalize($product, 'standard', Argument::any())->willReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
        ]);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
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

        $normalizer->normalize($productModel, 'standard', Argument::any())->willReturn([
            'code' => 'foo',
            'family_variant' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
        ]);

        $this->process($productModel)->shouldReturn([
            'code' => 'foo',
            'family_variant' => 'shoes',
            'description-en_US' => 'Shoes'
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
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute,
        ExecutionContext $executionContext,
        ValueCollectionInterface $valueCollection,
        ValueCollectionInterface $filteredValues
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

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

        $normalizer->normalize($product, 'standard', Argument::any())->willReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
        ]);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes'
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
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $user,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        AttributeInterface $attribute,
        ExecutionContext $executionContext,
        ValueCollectionInterface $valueCollection
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('admin');
        $userProvider->loadUserByUsername('admin')->willReturn($user);
        $user->getRoles()->willReturn([]);
        $tokenStorage->setToken(Argument::type(UsernamePasswordToken::class))->shouldBeCalled();

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

        $normalizer->normalize($product, 'standard', Argument::any())->willReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
        ]);

        $this->process($product)->shouldReturn([
            'sku' => 'foo',
            'family' => 'shoes',
            'description-en_US' => 'Shoes',
            'size' => '42'
        ]);
    }
}
