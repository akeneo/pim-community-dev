<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Pim\Permission\Bundle\EventSubscriber\SavePermissionsSubscriber;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager;
use Akeneo\Pim\Structure\Bundle\Event\AttributeGroupEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Exception\JobInstanceCannotBeUpdatedException;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;

class SavePermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager,
        GenericEvent $event,
        JobInstance $job
    ) {
        $this->beConstructedWith(
            $groupRepository,
            $attributeGroupAccessManager,
            $jobInstanceAccessManager
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SavePermissionsSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                JobInstanceEvents::PRE_SAVE    => 'checkJobInstancePermissions',
                AttributeGroupEvents::POST_SAVE => 'saveAttributeGroupPermissions',
                JobInstanceEvents::POST_SAVE    => 'saveJobInstancePermissions',
            ]
        );
    }

    function it_throw_exception_when_job_edit_permission_is_not_defined($event, $job)
    {
        $event->getSubject()->willReturn($job);
        $event->hasArgument('data')->willReturn(true);
        $event->getArgument('data')->willReturn([
            'permissions' => [
                'edit' => [],
            ]
        ]);

        $this->shouldThrow(new JobInstanceCannotBeUpdatedException('pim_import_export.entity.job_instance.flash.update.fail_empty_permission'))
            ->during('checkJobInstancePermissions', [$event]);
    }
}
