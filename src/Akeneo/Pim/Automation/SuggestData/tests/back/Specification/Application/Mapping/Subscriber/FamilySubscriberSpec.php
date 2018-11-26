<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Application\Connector\JobLauncherInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber\FamilySubscriber;
use Akeneo\Pim\Automation\SuggestData\Domain\FamilyAttribute\Query\FindFamilyAttributesNotInQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilySubscriberSpec extends ObjectBehavior
{
    public function let(FindFamilyAttributesNotInQueryInterface $query, JobLauncherInterface $jobLauncher): void
    {
        $this->beConstructedWith($query, $jobLauncher);
    }

    public function it_is_a_family_subscriber(): void
    {
        $this->shouldHaveType(FamilySubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_events(): void
    {
        $this::getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'updateAttributesMapping']);
    }

    public function it_updates_attributes_mapping_linked_to_the_removed_attributes_from_the_family(
        $query,
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(2);
        $family->getCode()->willReturn('shoes');
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'description', 'size', 'color']);

        $removedAttributes = ['brand', 'type'];
        $query
            ->findFamilyAttributesNotIn('shoes', ['sku', 'name', 'description', 'size', 'color'])
            ->willReturn($removedAttributes);

        $jobLauncher->launch(
            JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING,
            [
                'pim_attribute_codes' => $removedAttributes,
                'family_code' => 'shoes',
            ]
        )->shouldBeCalled();

        $this->updateAttributesMapping($event);
    }

    public function it_does_nothing_if_event_does_not_come_from_a_family(
        $query,
        $jobLauncher,
        GenericEvent $event,
        ProductInterface $product
    ): void {
        $event->getSubject()->willReturn($product);

        $query->findFamilyAttributesNotIn(Argument::cetera())->shouldNotBeCalled();
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updateAttributesMapping($event)->shouldReturn(null);
    }

    public function it_does_nothing_if_event_comes_from_a_newly_created_family(
        $query,
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(null);

        $query->findFamilyAttributesNotIn(Argument::cetera())->shouldNotBeCalled();
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updateAttributesMapping($event)->shouldReturn(null);
    }

    public function it_does_not_launch_the_job_if_no_attribute_has_been_removed_from_the_family(
        $query,
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(2);
        $family->getCode()->willReturn('shoes');
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'description', 'size', 'color']);

        $query
            ->findFamilyAttributesNotIn('shoes', ['sku', 'name', 'description', 'size', 'color'])
            ->willReturn([]);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updateAttributesMapping($event)->shouldReturn(null);
    }
}
