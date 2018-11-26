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

use Akeneo\Pim\Automation\SuggestData\Application\Launcher\JobLauncherInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber\AttributeOptionDeletedSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionDeletedSubscriberSpec extends ObjectBehavior
{
    public function let(JobLauncherInterface $jobLauncher): void
    {
        $this->beConstructedWith($jobLauncher);
    }

    public function it_is_a_product_family_removal_subscriber(): void
    {
        $this->shouldHaveType(AttributeOptionDeletedSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_publishes_a_new_job_in_the_job_queue(
        GenericEvent $event,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        $jobLauncher
    ): void {
        $event->getSubject()->willReturn($attributeOption);

        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');

        $jobLauncher->launch(AttributeOptionDeletedSubscriber::JOB_INSTANCE_NAME, [
            'pim_attribute_code' => 'color',
            'attribute_option_code' => 'red',
        ])->shouldBeCalled();

        $this->onPostRemove($event);
    }

    public function it_is_only_applied_when_an_attribute_option_is_removed(
        GenericEvent $event,
        $jobLauncher
    ): void {
        $event->getSubject()->willReturn(new \stdClass());

        $jobLauncher->launch(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }
}
