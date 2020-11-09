<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        NormalizerInterface $externalApiNormalizer
    ): void {
        $this->beConstructedWith($productModelRepository, $externalApiNormalizer);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductModelCreatedAndUpdatedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_product_model_created_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductModelCreated($author, ['data']))->shouldReturn(true);
    }

    public function it_supports_product_model_updated_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductModelUpdated($author, ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $this->supports(new ProductRemoved($author, ['data']))->shouldReturn(false);
        $this->supports(new ProductCreated($author, ['data']))->shouldReturn(false);
        $this->supports(new ProductUpdated($author, ['data']))->shouldReturn(false);
    }

    public function it_builds_product_created_event(
        $productModelRepository,
        $externalApiNormalizer,
        UserInterface $user
    ): void {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn($productModel);
        $externalApiNormalizer->normalize($productModel, 'external_api')->willReturn(['code' => 'polo_col_mao',]);

        $this->build(
            new ProductModelCreated($author, ['code' => 'polo_col_mao']),
            1
        )->shouldReturn(
            [
                'resource' => ['code' => 'polo_col_mao'],
            ]
        );
    }

    public function it_builds_product_updated_event(
        $productModelRepository,
        $externalApiNormalizer,
        UserInterface $user
    ): void {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn($productModel);
        $externalApiNormalizer->normalize($productModel, 'external_api')->willReturn(['code' => 'polo_col_mao',]);

        $this->build(
            new ProductModelUpdated($author, ['code' => 'polo_col_mao']),
            1
        )->shouldReturn(
            [
                'resource' => ['code' => 'polo_col_mao'],
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

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'build',
                [
                    new AnotherBusinessEvent($author, ['code' => 'polo_col_mao']),
                    1
                ]
            );
    }

    public function it_does_not_build_if_product_model_was_not_found($productModelRepository, UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);
        $author = Author::fromUser($user->getWrappedObject());

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn(null);

        $this->shouldThrow(ProductModelNotFoundException::class)
            ->during(
                'build',
                [
                    new ProductModelUpdated($author, ['code' => 'polo_col_mao']),
                    1
                ]
            );
    }

    public function it_raises_a_not_granted_category_exception($productModelRepository)
    {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $author = Author::fromNameAndType('julia', 'ui');

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn($productModel)->willThrow(
            AccessDeniedException::class
        );

        $this->shouldThrow(NotGrantedCategoryException::class)
            ->during(
                'build',
                [
                    new ProductModelCreated($author, ['code' => 'polo_col_mao']),
                    1
                ]
            );
    }
}

class AnotherBusinessEvent extends BusinessEvent
{
    public function name(): string
    {
        return 'another_business_event';
    }
}
