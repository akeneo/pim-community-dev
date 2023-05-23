<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ComputeProductIdentifiersSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeProductIdentifiersSubscriberSpec extends ObjectBehavior
{
    function let(
        Connection $connection
    ): void {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ComputeProductIdentifiersSubscriber::class);
    }

    function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_save_events(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    function it_should_update_the_identifiers_of_an_updated_product(
        Connection $connection,
        GenericEvent $event
    ): void
    {
        $product = new Product();
        $event->getSubject()->willReturn($product);
        $product->setValues(
            new WriteValueCollection([
                IdentifierValue::value('ean', true, 'toto'),
                ScalarValue::value('text', 'tutu'),
                IdentifierValue::value('iban', true, 'titi'),
            ])
        );

        $connection->executeStatement(
            Argument::any(),
            ['uuid' => $product->getUuid()->getBytes(), 'identifiers' => ['ean#toto', 'iban#titi']],
            Argument::any()
        )->shouldBeCalled();

        $this->fillProductIdentifiers($event);
    }

    function it_should_do_nothing_if_subject_is_not_a_product(
        GenericEvent $event,
        Connection $connection
    ): void {
        $subject = new ProductModel();
        $event->getSubject()->willReturn($subject);

        $connection->executeStatement(Argument::cetera())->shouldNotBeCalled();
        $this->fillProductIdentifiers($event);
    }

    function it_updates_a_product_without_identifiers(
        GenericEvent $event,
        Connection $connection
    ): void {
        $product = new Product();
        $event->getSubject()->willReturn($product);
        $product->setValues(
            new WriteValueCollection([
                ScalarValue::value('text', 'tutu'),
                ScalarValue::value('number', 12),
            ])
        );

        $connection->executeStatement(
            Argument::any(),
            ['uuid' => $product->getUuid()->getBytes(), 'identifiers' => []],
            Argument::any()
        )->shouldBeCalled();

        $this->fillProductIdentifiers($event);
    }
}