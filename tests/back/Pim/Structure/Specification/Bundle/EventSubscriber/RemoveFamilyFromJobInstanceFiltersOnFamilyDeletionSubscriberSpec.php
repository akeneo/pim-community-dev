<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveFamilyFromJobInstanceFiltersOnFamilyDeletionSubscriberSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $em, BulkSaverInterface $bulkSaver, ObjectRepository $repository)
    {
        $this->beConstructedWith($em, $bulkSaver);

        $em->getRepository(JobInstance::class)->willReturn($repository);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_REMOVE => 'removeDeletedFamilyFromExportJobInstancesFilters',
        ]);
    }

    function it_does_nothing_if_the_removed_object_is_not_a_family()
    {
        $this->removeDeletedFamilyFromExportJobInstancesFilters(new GenericEvent(new \StdClass()));
    }

    function it_does_not_update_export_job_instances_without_filter_on_families(
        BulkSaverInterface $bulkSaver,
        ObjectRepository $repository,
        GenericEvent $event,
        FamilyInterface $family,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn($family);
        $family->getCode()->willReturn('mugs');
        $repository->findBy(['type' => JobInstance::TYPE_EXPORT])->willReturn([$jobInstance]);

        $jobInstance->getRawParameters()->willReturn([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [[
                    'field' => 'categories',
                    'operator' => 'IN CHILDREN',
                    'value' => ['master'],
                ]],
            ],
        ]);

        $jobInstance->setRawParameters(Argument::any())->shouldNotBeCalled();
        $bulkSaver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->removeDeletedFamilyFromExportJobInstancesFilters($event);
    }

    function it_does_not_update_export_job_instances_without_filter_on_a_deleted_family(
        BulkSaverInterface $bulkSaver,
        ObjectRepository $repository,
        GenericEvent $event,
        FamilyInterface $family,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn($family);
        $family->getCode()->willReturn('mugs');
        $repository->findBy(['type' => JobInstance::TYPE_EXPORT])->willReturn([$jobInstance]);

        $jobInstance->getRawParameters()->willReturn([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [[
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['shoes', 'tshirts'],
                ]],
            ],
        ]);

        $jobInstance->setRawParameters(Argument::any())->shouldNotBeCalled();
        $bulkSaver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->removeDeletedFamilyFromExportJobInstancesFilters($event);
    }

    function it_removes_the_deleted_family_from_the_export_job_instance_filters(
        BulkSaverInterface $bulkSaver,
        ObjectRepository $repository,
        GenericEvent $event,
        FamilyInterface $family,
        JobInstance $jobInstanceA,
        JobInstance $jobInstanceB
    ) {
        $event->getSubject()->willReturn($family);
        $family->getCode()->willReturn('mugs');

        $repository->findBy(['type' => JobInstance::TYPE_EXPORT])->willReturn([
            $jobInstanceA,
            $jobInstanceB,
        ]);

        $jobInstanceA->getRawParameters()->willReturn([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [[
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['mugs', 'accessories'],
                ]],
            ],
        ]);

        $jobInstanceA->setRawParameters([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [[
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['accessories'],
                ]],
            ],
        ])->shouldBeCalled();

        $jobInstanceB->getRawParameters()->willReturn([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => ['master'],
                    ],
                    [
                        'field' => 'family',
                        'operator' => 'IN',
                        'value' => ['shoes', 'mugs', 'tshirts'],
                    ]
                ],
            ],
        ]);

        $jobInstanceB->setRawParameters([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => ['master'],
                    ],
                    [
                        'field' => 'family',
                        'operator' => 'IN',
                        'value' => ['shoes', 'tshirts'],
                    ]
                ],
            ],
        ])->shouldBeCalled();

        $bulkSaver->saveAll([$jobInstanceA, $jobInstanceB])->shouldBeCalled();

        $this->removeDeletedFamilyFromExportJobInstancesFilters($event);
    }

    function it_remove_the_filter_on_families_if_the_deleted_family_was_the_only_one(
        BulkSaverInterface $bulkSaver,
        ObjectRepository $repository,
        GenericEvent $event,
        FamilyInterface $family,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn($family);
        $family->getCode()->willReturn('mugs');
        $repository->findBy(['type' => JobInstance::TYPE_EXPORT])->willReturn([$jobInstance]);

        $jobInstance->getRawParameters()->willReturn([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => ['master'],
                    ],
                    [
                        'field' => 'family',
                        'operator' => 'IN',
                        'value' => ['mugs'],
                    ]
                ],
            ],
        ]);

        $jobInstance->setRawParameters([
            'delimiter' => ';',
            'enclosure' => '"',
            'filters' => [
                'data'=> [
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => ['master'],
                    ]
                ],
            ],
        ])->shouldBeCalled();

        $bulkSaver->saveAll([$jobInstance])->shouldBeCalled();

        $this->removeDeletedFamilyFromExportJobInstancesFilters($event);
    }
}
