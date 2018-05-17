<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Datagrid\MassAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\User\Model\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductFieldsBuilderSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $localeRepository,
            $currencyRepository,
            $assocTypeRepo,
            $catalogContext,
            $accessRepository,
            $tokenStorage
        );
    }

    function it_does_not_filters_empty_attributes($productRepository)
    {
        $productRepository->getAvailableAttributeIdsToExport(['foo', 'bar'])->willReturn([]);

        $this->getFieldsList(['foo', 'bar'])->shouldReturn([]);
        $this->getAttributeIds()->shouldReturn([]);
    }

    function it_filters_attributes_based_on_user_access(
        $productRepository,
        $accessRepository,
        $attributeRepository,
        $user,
        $assocTypeRepo,
        AttributeInterface $attribute,
        AssociationTypeInterface $association
    ) {
        $association->getCode()->willReturn('association-type-code');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getType()->willReturn(null);
        $attribute->getCode()->willReturn('attribute-code');

        $assocTypeRepo->findAll()->willReturn([$association]);
        $attributeRepository->findBy(['id' => ['baz']])->willReturn([$attribute]);
        $productRepository->getAvailableAttributeIdsToExport(['foo', 'bar'])->willReturn(['fooz', 'baz']);
        $accessRepository->getGrantedAttributeIds($user, Attributes::VIEW_ATTRIBUTES, ['fooz', 'baz'])
            ->willReturn(['baz']);

        $this->getFieldsList(['foo', 'bar'])->shouldReturn([
            "attribute-code",
            "family",
            "categories",
            "groups",
            "association-type-code-groups",
            "association-type-code-products",
        ]);

        $this->getAttributeIds()->shouldReturn(['baz']);
    }
}
