<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Updater\Setter\GrantedCategoryFieldSetter;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class GrantedCategoryFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepository,
        ObjectManager $entityManager
    ) {
        $this->beConstructedWith(
            $categoryFieldSetter,
            $authorizationChecker,
            $categoryAccessRepository,
            $tokenStorage,
            $entityManager,
            ['categories']
        );
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GrantedCategoryFieldSetter::class);
    }

    function it_sets_categories(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn([]);
        $product->getUuid()->willReturn(Uuid::uuid4());
        $categoryAccessRepository->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, [])->willReturn(true);

        $product->getCategoriesForVariation()->willReturn([$categoryA, $categoryB]);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $this->shouldNotThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                GrantedCategoryFieldSetter::class,
                'categoryB'
            )
        )->during('setFieldData', [$product, 'categories', $data, []]);

        $this->shouldNotThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                GrantedCategoryFieldSetter::class,
                'categoryA'
            )
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_throws_an_exception_if_user_lose_ownership_because_there_is_still_a_category_the_user_can_not_view(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        $entityManager,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        ProductInterface $fullProduct,
        ProductRepositoryInterface $productRepository
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn(['categoryA', 'categoryB', 'categoryC']);
        $categoryAccessRepository
            ->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, ['categoryA', 'categoryB', 'categoryC'])
            ->willReturn(false);

        $entityManager->getRepository(Argument::any())->willReturn($productRepository);
        $uuid = Uuid::uuid4();
        $productRepository->find($uuid)->willReturn($fullProduct);
        $fullProduct->getCategoryCodes()->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $product->getUuid()->willReturn($uuid);
        $categories = new ArrayCollection();
        $product->getCategoriesForVariation()->willReturn($categories);
        $product->getCategories()->willReturn($categories);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $this->shouldThrow(
            new InvalidArgumentException('You should at least keep your product in one category on which you have an own permission.')
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_throws_an_exception_if_a_category_is_not_granted(
        $authorizationChecker,
        $tokenStorage,
        $entityManager,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB,
        ProductInterface $fullProduct,
        ProductRepositoryInterface $productRepository
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn([]);
        $categoryAccessRepository->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, ['categoryA', 'categoryB', 'categoryC'])
            ->willReturn(true);

        $categoryB->getCode()->willReturn('categoryB');
        $fullProduct->getCategoryCodes()->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $entityManager->getRepository(Argument::any())->willReturn($productRepository);
        $uuid = Uuid::uuid4();
        $productRepository->find($uuid)->willReturn($fullProduct);

        $product->getUuid()->willReturn($uuid);
        $categories = new ArrayCollection();
        $categories->add($categoryA->getWrappedObject());
        $categories->add($categoryB->getWrappedObject());
        $product->getCategoriesForVariation()->willReturn($categories);
        $product->getCategories()->willReturn($categories);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::OWN_PRODUCTS, $categoryA)->willReturn(true);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                GrantedCategoryFieldSetter::class,
                'categoryB'
            )
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_throws_an_exception_if_user_loses_the_ownership_on_a_product(
        $authorizationChecker,
        $tokenStorage,
        $entityManager,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        ProductInterface $fullProduct,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB,
        ProductRepositoryInterface $productRepository
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn([]);
        $categoryAccessRepository
            ->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, [])
            ->willReturn(true);

        $categoryB->getCode()->willReturn('categoryB');
        $fullProduct->getCategoryCodes()->willReturn([]);

        $entityManager->getRepository(Argument::any())->willReturn($productRepository);
        $uuid = Uuid::uuid4();
        $productRepository->find($uuid)->willReturn($fullProduct);

        $product->getUuid()->willReturn($uuid);
        $categories = new ArrayCollection();
        $categories->add($categoryA->getWrappedObject());
        $categories->add($categoryB->getWrappedObject());
        $product->getCategoriesForVariation()->willReturn($categories);
        $product->getCategories()->willReturn($categories);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN_PRODUCTS, $categoryA)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::OWN_PRODUCTS, $categoryB)->willReturn(false);

        $this->shouldThrow(
            new InvalidArgumentException('You should at least keep your product in one category on which you have an own permission.')
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_not_throws_an_exception_when_you_changed_to_a_category_editable_for_product_model(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        $entityManager,
        TokenInterface $token,
        UserInterface $user,
        ProductModelInterface $productModel,
        ProductModelInterface $fullProductModel,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $productModel->getCategoryCodes()->willReturn(['categoryA', 'categoryB', 'categoryC']);
        $categoryAccessRepository
            ->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, ['categoryA', 'categoryB', 'categoryC'])
            ->willReturn(false);

        $entityManager->getRepository(Argument::any())->willReturn($productModelRepository);
        $productModelRepository->find(1)->willReturn($fullProductModel);
        $fullProductModel->getCategoryCodes()->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $productModel->getId()->willReturn(1);
        $productModel->getCategories()->willReturn([]);
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(false);

        $this->shouldNotThrow(
            new InvalidArgumentException('You should at least keep your product in one category on which you have an own permission.')
        )->during('setFieldData', [$productModel, 'categories', $data, []]);
    }

}
