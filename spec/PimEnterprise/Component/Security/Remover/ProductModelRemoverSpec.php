<?php

namespace spec\PimEnterprise\Component\Security\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use PimEnterprise\Component\Security\Remover\ProductModelRemover;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductModelRemoverSpec extends ObjectBehavior
{
    function let(
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith($remover, $bulkRemover, $authorizationChecker, $productModelRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelRemover::class);
    }

    function it_removes_a_productModel(
        ProductModelInterface $productModel,
        $remover,
        $authorizationChecker,
        $productModelRepository,
        ProductModelInterface $fullProductModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(true);

        $options = ['option' => 'foo'];

        $productModel->getCode()->willReturn('code');
        $productModelRepository->findOneByIdentifier('code')->willReturn($fullProductModel);

        $remover->remove($fullProductModel, $options)->shouldBeCalled();
        $this->remove($productModel, $options);
    }

    function it_removes_a_list_of_productModels(
        ProductModelInterface $firstProductModel,
        ProductModelInterface $secondProductModel,
        $bulkRemover,
        $authorizationChecker,
        $productModelRepository,
        ProductModelInterface $fullFirstProductModel,
        ProductModelInterface $fullSecondProductModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $firstProductModel)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $secondProductModel)->willReturn(true);

        $firstProductModel->getCode()->willReturn('code1');
        $productModelRepository->findOneByIdentifier('code1')->willReturn($fullFirstProductModel);

        $secondProductModel->getCode()->willReturn('code2');
        $productModelRepository->findOneByIdentifier('code2')->willReturn($fullSecondProductModel);

        $productModels = [$fullFirstProductModel, $fullSecondProductModel];
        $options = ['option' => 'foo'];

        $bulkRemover->removeAll($productModels, $options)->shouldBeCalled();
        $this->removeAll([$firstProductModel, $secondProductModel], $options);
    }

    function it_throws_an_exception_when_the_object_to_remove_is_not_a_productModel()
    {
        $invalidProductModel = new \stdClass();

        $this->shouldThrow(InvalidObjectException::objectExpected('stdClass', 'Pim\Component\Catalog\Model\ProductModelInterface'))
            ->during('remove', [$invalidProductModel]);
    }

    function it_throws_an_exception_when_one_of_the_objects_to_remove_is_not_a_productModel(ProductModelInterface $firstProductModel, $authorizationChecker)
    {
        $secondProductModel = new \stdClass();
        $productModels = [$firstProductModel, $secondProductModel];

        $authorizationChecker->isGranted(Attributes::OWN, $firstProductModel)->willReturn(true);


        $this->shouldThrow(InvalidObjectException::objectExpected('stdClass', 'Pim\Component\Catalog\Model\ProductModelInterface'))
            ->during('removeAll', [$productModels]);
    }

    function it_throws_an_exception_when_the_user_is_not_authorized_to_remove_the_productModel(ProductModelInterface $productModel, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $productModel->getWrappedObject(),
                'You can delete a product model only if it is classified in at least one category on which you have an own permission.'
            )
        )->during('remove', [$productModel]);
    }

    function it_throws_an_exception_when_the_user_is_not_authorized_to_remove_one_of_the_productModels(
        ProductModelInterface $firstProductModel,
        ProductModelInterface $secondProductModel,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $firstProductModel)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $secondProductModel)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $secondProductModel->getWrappedObject(),
                'You can delete a product model only if it is classified in at least one category on which you have an own permission.'
            )
        )->during('removeAll', [[$firstProductModel, $secondProductModel]]);
    }
}
