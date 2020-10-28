<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCreatedAndUpdatedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        NormalizerInterface $externalApiNormalizer
    ): void {
        $this->beConstructedWith($productRepository, $externalApiNormalizer);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductCreatedAndUpdatedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_product_created_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductCreated($author, ['data']))->shouldReturn(true);
    }

    public function it_supports_product_updated_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductUpdated($author, ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductRemoved($author, ['data']))->shouldReturn(false);
    }


    public function it_builds_product_created_event(
        $productRepository,
        $externalApiNormalizer,
        UserInterface $user
    ): void {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);
        $externalApiNormalizer->normalize($product, 'external_api')->willReturn(
            [
                'identifier' => 'product_identifier',
            ]
        );

        $this->build(new ProductCreated($author, ['identifier' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_builds_product_updated_event(
        $productRepository,
        $externalApiNormalizer,
        UserInterface $user
    ): void {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);
        $externalApiNormalizer->normalize($product, 'external_api')->willReturn(
            [
                'identifier' => 'product_identifier',
            ]
        );

        $this->build(new ProductUpdated($author, ['identifier' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(UserInterface $user): void
    {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [new ProductRemoved($author, ['identifier' => 'product_identifier'])]);
    }

    public function it_does_not_build_if_product_was_not_found($productRepository, UserInterface $user): void
    {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->shouldThrow(ProductNotFoundException::class)
            ->during('build', [new ProductCreated($author, ['identifier' => 'product_identifier'])]);
    }

    public function it_raises_a_not_granted_category_exception($productRepository)
    {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $productRepository->findOneByIdentifier('product_identifier')->willThrow(AccessDeniedException::class);

        $this->shouldThrow(NotGrantedCategoryException::class)
            ->during('build', [new ProductCreated('julia', ['identifier' => 'product_identifier'])]);
    }
}
