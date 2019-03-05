<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\ProductAssetBundle\Event\VariationHasBeenCreated;
use PimEnterprise\Bundle\ProductAssetBundle\Event\VariationHasBeenDeleted;
use PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ComputeCompletenessOfProductsLinkedToAssetsSubscriber;
use PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ComputeCompletenessOfProductsLinkedToAssetsSubscriberSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        CompletenessRemoverInterface $completenessRemover,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $this->beConstructedWith(
            $jobInstanceRepository,
            $attributeRepository,
            $productQueryBuilderFactory,
            $jobLauncher,
            $tokenStorage,
            $completenessRemover
        );
    }

    function it_is_a_compute_completeness_of_products_linked_to_assets_subscriber(): void
    {
        $this->shouldBeAnInstanceOf(ComputeCompletenessOfProductsLinkedToAssetsSubscriber::class);
        $this->shouldBeAnInstanceOf(EventSubscriberInterface::class);
    }

    function it_subscribes_to_multiple_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(AssetEvent::POST_UPLOAD_FILES);
        $this::getSubscribedEvents()->shouldHaveKey(VariationHasBeenDeleted::VARIATION_HAS_BEEN_DELETED);
        $this::getSubscribedEvents()->shouldHaveKey(VariationHasBeenCreated::VARIATION_HAS_BEEN_CREATED);
    }

    function it_removes_completenesses_if_compute_job_instance_does_not_exist(
        $jobInstanceRepository,
        $jobLauncher,
        $completenessRemover,
        AssetInterface $asset
    ): void {
        $jobInstanceRepository->findOneByIdentifier('compute_completeness_of_products_linked_to_assets')
                              ->willReturn(null);
        $completenessRemover->removeForAsset($asset)->shouldBeCalled();
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->onFilesUploaded(new AssetEvent($asset->getWrappedObject()));
    }

    function it_does_not_launch_the_compute_completeness_job_if_no_product_is_impacted(
        $jobInstanceRepository,
        $attributeRepository,
        $productQueryBuilderFactory,
        $jobLauncher,
        $completenessRemover,
        AssetInterface $asset,
        JobInstance $jobInstance,
        ProductQueryBuilderInterface $pqb1,
        ProductQueryBuilderInterface $pqb2,
        CursorInterface $productCursor1,
        CursorInterface $productCursor2
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier('compute_completeness_of_products_linked_to_assets')
            ->willReturn($jobInstance);
        $asset->getCode()->willReturn('my_asset_code');
        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)
            ->willReturn(['assets', 'other_assets']);

        $productQueryBuilderFactory->create()->willReturn($pqb1, $pqb2);

        $pqb1->addFilter('assets', Operators::IN_LIST, ['my_asset_code'])->shouldBeCalled();
        $pqb1->execute()->willReturn($productCursor1);
        $productCursor1->count()->willReturn(0);

        $pqb2->addFilter('other_assets', Operators::IN_LIST, ['my_asset_code'])->shouldBeCalled();
        $pqb2->execute()->willReturn($productCursor2);
        $productCursor2->count()->willReturn(0);

        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->onFilesUploaded(new AssetEvent($asset->getWrappedObject()));
    }

    function it_launches_the_compute_completeness_job_on_post_upload_files(
        $jobInstanceRepository,
        $attributeRepository,
        $productQueryBuilderFactory,
        $jobLauncher,
        $completenessRemover,
        ProductQueryBuilderInterface $pqb,
        AssetInterface $asset,
        JobInstance $jobInstance,
        CursorInterface $productCursor
    ): void {
        $jobInstanceRepository->findOneByIdentifier('compute_completeness_of_products_linked_to_assets')
                              ->willReturn($jobInstance);
        $asset->getCode()->willReturn('asset_code_1');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)
            ->willReturn(['assets']);

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('assets', Operators::IN_LIST, ['asset_code_1'])->shouldBeCalled();
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(25);

        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $jobLauncher->launch($jobInstance, Argument::type(UserInterface::class), ['asset_codes' => ['asset_code_1']])
                    ->shouldBeCalled();

        $this->onFilesUploaded(new AssetEvent($asset->getWrappedObject()));
    }

    function it_launches_the_compute_completeness_job_on_post_upload_of_multiple_files(
        $jobInstanceRepository,
        $attributeRepository,
        $productQueryBuilderFactory,
        $jobLauncher,
        $completenessRemover,
        JobInstance $jobInstance,
        AssetInterface $asset1,
        AssetInterface $asset2,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ): void {
        $jobInstanceRepository->findOneByIdentifier('compute_completeness_of_products_linked_to_assets')
                              ->willReturn($jobInstance);
        $asset1->getCode()->willReturn('asset_code_1');
        $asset2->getCode()->willReturn('asset_code_2');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)
            ->willReturn(['assets']);

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('assets', Operators::IN_LIST, ['asset_code_1', 'asset_code_2'])->shouldBeCalled();
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(25);

        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $jobLauncher->launch(
            $jobInstance,
            Argument::type(UserInterface::class),
            ['asset_codes' => ['asset_code_1', 'asset_code_2']]
        )->shouldBeCalled();

        $this->onFilesUploaded(
            new AssetEvent(
                [
                    $asset1->getWrappedObject(),
                    $asset2->getWrappedObject(),
                ]
            )
        );
    }

    function it_launches_the_compute_completeness_job_on_variation_creation(
        $jobInstanceRepository,
        $attributeRepository,
        $productQueryBuilderFactory,
        $jobLauncher,
        $completenessRemover,
        JobInstance $jobInstance,
        AssetInterface $asset,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ): void {
        $jobInstanceRepository->findOneByIdentifier('compute_completeness_of_products_linked_to_assets')
            ->willReturn($jobInstance);
        $asset->getCode()->willReturn('asset_code_1');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)
            ->willReturn(['assets']);

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('assets', Operators::IN_LIST, ['asset_code_1'])->shouldBeCalled();
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(25);

        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $jobLauncher->launch(
            $jobInstance,
            Argument::type(UserInterface::class),
            ['asset_codes' => ['asset_code_1']]
        )->shouldBeCalled();

        $this->onVariationCreated(new VariationHasBeenCreated($asset->getWrappedObject()));
    }

    function it_launches_the_compute_completeness_job_on_variation_deletion(
        $jobInstanceRepository,
        $attributeRepository,
        $productQueryBuilderFactory,
        $jobLauncher,
        $completenessRemover,
        JobInstance $jobInstance,
        AssetInterface $asset,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ): void {
        $jobInstanceRepository->findOneByIdentifier('compute_completeness_of_products_linked_to_assets')
            ->willReturn($jobInstance);
        $asset->getCode()->willReturn('asset_code_1');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)
            ->willReturn(['assets']);

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('assets', Operators::IN_LIST, ['asset_code_1'])->shouldBeCalled();
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(25);

        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $jobLauncher->launch(
            $jobInstance,
            Argument::type(UserInterface::class),
            ['asset_codes' => ['asset_code_1']]
        )->shouldBeCalled();

        $this->onVariationDeleted(new VariationHasBeenDeleted($asset->getWrappedObject()));
    }
}
