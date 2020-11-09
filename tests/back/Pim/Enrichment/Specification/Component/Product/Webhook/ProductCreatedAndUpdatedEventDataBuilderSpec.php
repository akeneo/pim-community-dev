<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
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
        GetConnectorProducts $getConnectorProductsQuery,
        NormalizerInterface $externalApiNormalizer
    ): void {
        $this->beConstructedWith($getConnectorProductsQuery, $externalApiNormalizer);
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
        GetConnectorProducts $getConnectorProductsQuery,
        NormalizerInterface $externalApiNormalizer,
        UserInterface $user
    ): void {
        $product = new ConnectorProduct(
            1,
            'product_identifier',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection([])
        );

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $getConnectorProductsQuery->fromProductIdentifier('product_identifier', 10)->willReturn($product);
        $externalApiNormalizer->normalize($product, 'external_api')->willReturn(
            [
                'identifier' => 'product_identifier',
            ]
        );

        $this->build(
            new ProductCreated($author, ['identifier' => 'product_identifier']),
            10
        )->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_builds_product_updated_event(
        GetConnectorProducts $getConnectorProductsQuery,
        NormalizerInterface $externalApiNormalizer,
        UserInterface $user
    ): void {
        $product = new ConnectorProduct(
            1,
            'product_identifier',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection([])
        );

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $getConnectorProductsQuery->fromProductIdentifier('product_identifier', 10)->willReturn($product);
        $externalApiNormalizer->normalize($product, 'external_api')->willReturn(
            [
                'identifier' => 'product_identifier',
            ]
        );

        $this->build(
            new ProductUpdated($author, ['identifier' => 'product_identifier']),
            10
        )->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(UserInterface $user): void
    {
        $product = new ConnectorProduct(
            1,
            'product_identifier',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection([])
        );

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'build',
                [
                    new ProductRemoved($author, ['identifier' => 'product_identifier']),
                    1
                ]
            );
    }

    public function it_does_not_build_if_product_was_not_found(
        GetConnectorProducts $getConnectorProductsQuery,
        UserInterface $user
    ): void {
        $getConnectorProductsQuery->fromProductIdentifier('product_identifier', 10)->willReturn(null);

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->shouldThrow(ProductNotFoundException::class)
            ->during(
                'build',
                [
                    new ProductCreated($author, ['identifier' => 'product_identifier']),
                    1
                ]
            );
    }

    public function it_raises_a_not_granted_category_exception(GetConnectorProducts $getConnectorProductsQuery)
    {
        $product = new ConnectorProduct(
            1,
            'product_identifier',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection([])
        );
        $author = Author::fromNameAndType('julia', 'ui');

        $getConnectorProductsQuery->fromProductIdentifier('product_identifier', 10)->willThrow(ObjectNotFoundException::class);

        $this->shouldThrow(NotGrantedCategoryException::class)
            ->during(
                'build',
                [
                    new ProductCreated($author, ['identifier' => 'product_identifier']),
                    10
                ]
            );
    }
}
