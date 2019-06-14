<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\FranklinEvent;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\FranklinEvent\FranklinAttributeCreatedSubscriber;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeCreatedSubscriberSpec extends ObjectBehavior
{
    public function let(FranklinAttributeCreatedRepositoryInterface $repository): void
    {
        $this->beConstructedWith($repository);
    }

    public function it_is_a_franklin_attribute_created_subscriber(): void
    {
        $this->shouldHaveType(FranklinAttributeCreatedSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_on_franklin_attribute_created_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([FranklinAttributeCreated::EVENT_NAME => 'persist']);
    }

    public function it_persists_franklin_attribute_created_event($repository): void
    {
        $attributeCreatedEvent = new FranklinAttributeCreated(new AttributeCode('color'), new AttributeType('pim_catalog_text'));

        $repository->save($attributeCreatedEvent)->shouldBeCalled();

        $this->persist($attributeCreatedEvent);
    }
}
