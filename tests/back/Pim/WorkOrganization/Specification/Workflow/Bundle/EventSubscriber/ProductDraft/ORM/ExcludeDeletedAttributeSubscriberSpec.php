<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft\ORM;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindExistingAttributeCodesQuery;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

class ExcludeDeletedAttributeSubscriberSpec extends ObjectBehavior
{
    function let(FindExistingAttributeCodesQuery $query)
    {
        $this->beConstructedWith($query);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_excludes_unexistant_attributes(
        $query,
        EntityWithValuesDraftInterface $productDraft,
        LifecycleEventArgs $args
    ) {
        $dbData = [
            'values' => [
                'name' => ['data' => 'Pipoux', 'locale' => null, 'scope' => null],
                'description' => ['data' => 'undefined', 'locale' => null, 'scope' => null],
                'something' => ['status' => 'draft', 'locale' => null, 'scope' => null],
            ],
            'review_statuses' => [
                'name' => ['status' => 'draft', 'locale' => null, 'scope' => null],
                'something' => ['status' => 'draft', 'locale' => null, 'scope' => null],
            ]
        ];

        $args->getObject()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn($dbData);

        $query->execute(['name', 'description', 'something'])->willReturn(['name', 'description']);

        unset($dbData['values']['something'], $dbData['review_statuses']['something']);
        $productDraft->setChanges($dbData)->shouldBeCalled();

        $this->postLoad($args);
    }
}
