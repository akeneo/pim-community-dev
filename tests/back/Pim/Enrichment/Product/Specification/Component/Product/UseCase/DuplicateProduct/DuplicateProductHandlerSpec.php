<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProduct;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProductHandler;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProductResponse;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\RemoveUniqueAttributeValues;
use Akeneo\Pim\Permission\Component\Authorization\FetchUserRightsOnProductInterface;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductUuid;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DuplicateProductHandlerSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RemoveUniqueAttributeValues $removeUniqueAttributeValues,
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        SaverInterface $productSaver,
        SecurityFacade $securityFacade,
        FetchUserRightsOnProductInterface $fetchUserRightsOnProduct
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $removeUniqueAttributeValues,
            $productBuilder,
            $normalizer,
            $productUpdater,
            $validator,
            $productSaver,
            $securityFacade,
            $fetchUserRightsOnProduct
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DuplicateProductHandler::class);
    }

    function it_can_duplicate_a_product(
        $securityFacade,
        $fetchUserRightsOnProduct,
        $productRepository,
        $attributeRepository,
        $productBuilder,
        $productUpdater,
        ProductInterface $productToDuplicate,
        ProductInterface $duplicatedProduct
    ) {
        $productUuid = Uuid::uuid4();
        $productRepository->find($productUuid)->willReturn($productToDuplicate);
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $productBuilder->createProduct(Argument::cetera())->willReturn($duplicatedProduct);
        $productUpdater->update($duplicatedProduct, [
            'values' => [
                'sku' => [
                    0 => ['data' => 'duplicated_product', 'locale' => null, 'scope' => null],
                ],
            ],
        ])->shouldBeCalled();

        $userRightsOnProduct = new UserRightsOnProductUuid(
            $productUuid,
            1,
            1,
            1,
            1,
            1
        );
        $duplicateProductCommand = new DuplicateProduct(
            $productUuid,
            'duplicated_product',
            1
        );

        $fetchUserRightsOnProduct->fetchByUuid(Argument::type(UuidInterface::class), Argument::type('integer'))->willReturn($userRightsOnProduct);
        $securityFacade->isGranted(Argument::type('string'))->willReturn(true);

        $this->shouldNotThrow(ObjectNotFoundException::class)->during('handle', [$duplicateProductCommand]);
    }

    function it_throws_an_error_when_the_user_does_not_have_the_editable_right(
        $securityFacade,
        $fetchUserRightsOnProduct
    ) {
        $productUuid = Uuid::uuid4();
        $userRightsOnProduct = new UserRightsOnProductUuid(
            $productUuid,
            1,
            0,
            0,
            0,
            1
        );
        $duplicateProductCommand = new DuplicateProduct(
            $productUuid,
            'duplicated_product',
            1
        );

        $fetchUserRightsOnProduct->fetchByUuid(Argument::type(UuidInterface::class), Argument::type('integer'))->willReturn($userRightsOnProduct);
        $securityFacade->isGranted(Argument::type('string'))->willReturn(true);

        $this->shouldThrow(ObjectNotFoundException::class)->during('handle', [$duplicateProductCommand]);
    }

    function it_throws_an_error_when_the_user_does_not_have_the_acls(
        $securityFacade,
        $fetchUserRightsOnProduct
    ) {
        $productUuid = Uuid::uuid4();
        $userRightsOnProduct = new UserRightsOnProductUuid(
            $productUuid,
            1,
            1,
            1,
            1,
            1
        );
        $duplicateProductCommand = new DuplicateProduct(
            $productUuid,
            'duplicated_product',
            1
        );

        $fetchUserRightsOnProduct->fetchByUuid(Argument::type(UuidInterface::class), Argument::type('integer'))->willReturn($userRightsOnProduct);
        $securityFacade->isGranted(Argument::type('string'))->willReturn(false);

        $this->shouldThrow(ObjectNotFoundException::class)->during('handle', [$duplicateProductCommand]);
    }
}
