<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelRemovedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;

class ProductModelRemovedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith();
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductModelRemovedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_product_model_removed_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductModelRemoved($author, ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductCreated($author, ['data']))->shouldReturn(false);
    }

    public function it_builds_product_model_removed_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->build(
            new ProductModelRemoved($author, ['code' => 'product_identifier']),
            1
        )->shouldReturn(
            [
                'resource' => ['code' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->shouldThrow(new \InvalidArgumentException())->during(
            'build',
            [
                new ProductCreated($author, ['code' => 'product_identifier']),
                1
            ]
        );
    }
}
