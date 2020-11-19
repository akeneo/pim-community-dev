<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
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

    public function it_supports_product_model_created_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->supports(new ProductModelCreated($author, ['data']))->shouldReturn(true);
    }

    public function it_supports_product_model_updated_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->supports(new ProductModelUpdated($author, ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->supports(new ProductCreated($author, ['identifier' => '1']))->shouldReturn(false);
    }

    public function it_builds_product_model_created_event(
        $productModelRepository,
        $externalApiNormalizer,
        UserInterface $user
    ): void {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new ProductModelCreated($author, ['code' => 'polo_col_mao']);

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn($productModel);
        $externalApiNormalizer->normalize($productModel, 'external_api')->willReturn(['code' => 'polo_col_mao']);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($event, ['resource' => ['code' => 'polo_col_mao']]);

        $collection = $this->build($event, $user)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    public function it_builds_product_model_updated_event(
        $productModelRepository,
        $externalApiNormalizer,
        UserInterface $user
    ): void {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new ProductModelUpdated($author, ['code' => 'polo_col_mao']);

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn($productModel);
        $externalApiNormalizer->normalize($productModel, 'external_api')->willReturn(['code' => 'polo_col_mao']);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($event, ['resource' => ['code' => 'polo_col_mao']]);

        $collection = $this->build($event, $user)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    public function it_does_not_build_other_business_event(UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            new ProductCreated($author, ['identifier' => '1']),
            $user,
        ]);
    }

    public function it_does_not_build_if_product_model_was_not_found($productModelRepository, UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $productModelRepository->findOneByIdentifier('polo_col_mao')->willReturn(null);

        $this->shouldThrow(ProductModelNotFoundException::class)->during('build', [
            new ProductModelUpdated($author, ['code' => 'polo_col_mao']),
            $user,
        ]);
    }

    public function it_raises_a_not_granted_category_exception($productModelRepository, UserInterface $user)
    {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $author = Author::fromNameAndType('julia', 'ui');

        $productModelRepository
            ->findOneByIdentifier('polo_col_mao')
            ->willReturn($productModel)
            ->willThrow(AccessDeniedException::class);

        $this->shouldThrow(NotGrantedCategoryException::class)->during('build', [
            new ProductModelCreated($author, ['code' => 'polo_col_mao']),
            $user,
        ]);
    }
}
