<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductFieldsBuilderSpec extends ObjectBehavior
{
    function let(
        ProductManagerInterface $productManager,
        LocaleRepositoryInterface $localeRepository,
        CurrencyManager $currencyManager,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        SecurityContextInterface $securityContext,
        ProductRepositoryInterface $productRepository,
        ObjectRepository $attributeRepository,
        TokenInterface $token,
        UserInterface $user
    ) {
        $productManager->getProductRepository()->willReturn($productRepository);
        $productManager->getAttributeRepository()->willReturn($attributeRepository);
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $productManager,
            $localeRepository,
            $currencyManager,
            $assocTypeRepo,
            $catalogContext,
            $accessRepository,
            $securityContext
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
        $attribute->getAttributeType()->willReturn(null);
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
