<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Bundle\EventSubscriber;

use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Pim\Enrichment\AssetManager\Bundle\EventSubscriber\RemoveNonExistentAssetValuesSubscriber;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RemoveNonExistentAssetValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance,
        Connection $connection,
        TokenInterface $token,
        UserInterface $julia,
        Statement $statement
    ) {
        $connection->executeQuery(
            'SELECT code, properties FROM pim_catalog_attribute WHERE attribute_type = :type',
            [
                'type' => AssetCollectionType::ASSET_COLLECTION,
            ]
        )->willReturn($statement);
        $statement->fetchAll()->willReturn(
            [
                [
                    'code' => 'packshot_assets',
                    'properties' => \serialize(['reference_data_name' => 'packshot']),
                ],
                [
                    'code' => 'other_packshot_attribute',
                    'properties' => \serialize(['reference_data_name' => 'packshot']),
                ],
            ]
        );

        $token->getUser()->willReturn($julia);
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $jobInstanceRepository, $jobLauncher, $createJobInstance, $connection);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveNonExistentAssetValuesSubscriber::class);
    }

    function it_subscribes_to_deleted_assets_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(AssetDeletedEvent::class);
        $this::getSubscribedEvents()->shouldHaveKey(AssetsDeletedEvent::class);
    }

    function it_does_nothing_if_no_asset_collection_attribute_is_linked_to_the_asset_family(
        JobLauncherInterface $jobLauncher
    ) {
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();
        $this->onAssetDeleted(
            new AssetDeletedEvent(
                AssetIdentifier::fromString('userguide_pdf_1'),
                AssetCode::fromString('pdf_1'),
                AssetFamilyIdentifier::fromString('userguide')
            )
        );
    }

    function it_launches_one_job_per_attribute_linked_to_the_asset_family(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        UserInterface $julia,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->findOneByIdentifier('remove_non_existing_product_values')->willReturn($jobInstance);

        $jobLauncher->launch(
            $jobInstance,
            $julia,
            [
                'attribute_code' => 'packshot_assets',
                'attribute_options' => ['packshot_1'],
            ]
        )->shouldBeCalled();
        $jobLauncher->launch(
            $jobInstance,
            $julia,
            [
                'attribute_code' => 'other_packshot_attribute',
                'attribute_options' => ['packshot_1'],
            ]
        )->shouldBeCalled();

        $this->onAssetDeleted(
            new AssetDeletedEvent(
                AssetIdentifier::fromString('packshot_packshot_1'),
                AssetCode::fromString('packshot_1'),
                AssetFamilyIdentifier::fromString('packshot')
            )
        );
    }

    function it_launches_a_job_when_deleting_all_assets_of_a_family(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        UserInterface $julia,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->findOneByIdentifier('remove_non_existing_product_values')->willReturn($jobInstance);

        $jobLauncher->launch(
            $jobInstance,
            $julia,
            [
                'attribute_code' => 'packshot_assets',
                'attribute_options' => [],
            ]
        )->shouldBeCalled();
        $jobLauncher->launch(
            $jobInstance,
            $julia,
            [
                'attribute_code' => 'other_packshot_attribute',
                'attribute_options' => [],
            ]
        )->shouldBeCalled();

        $this->onBulkAssetsDeleted(
            new AssetsDeletedEvent(
                AssetFamilyIdentifier::fromString('packshot'),
                []
            )
        );
    }
}
