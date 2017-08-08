<?php

namespace spec\PimEnterprise\Component\Catalog\Manager;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\Catalog\Manager\AttributeValuesResolver;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AttributeValuesResolverSpec extends ObjectBehavior
{
    function let(
        AttributeValuesResolverInterface $attributeValueResolver,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository

    ) {
        $this->beConstructedWith($attributeValueResolver, $authorizationChecker, $attributeRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeValuesResolver::class);
        $this->shouldImplement(AttributeValuesResolverInterface::class);
    }

    function it_resolves_eligible_values_for_a_set_of_attributes(
        $attributeValueResolver,
        $authorizationChecker,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $desc,
        LocaleInterface $fr,
        LocaleInterface $en,
        ChannelInterface $ecom,
        ChannelInterface $print
    ) {
        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $attributeRepository->findOneByIdentifier('description')->willReturn($desc);

        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($fr);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($en);

        $print->getCode()->willReturn('print');
        $print->getLocales()->willReturn([$en, $fr]);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $sku)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $name)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $desc)->willReturn(true);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $fr)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $en)->willReturn(true);

        $attributeValueResolver
            ->resolveEligibleValues([$sku, $name, $desc], ['ecommerce'], ['fr_FR', 'en_US'])
            ->willReturn(
                [
                    [
                        'attribute' => 'sku',
                        'type' => 'pim_catalog_identifier',
                        'locale' => null,
                        'scope' => null
                    ],
                    [
                        'attribute' => 'name',
                        'type' => 'pim_catalog_text',
                        'locale' => 'fr_FR',
                        'scope' => null
                    ],
                    [
                        'attribute' => 'name',
                        'type' => 'pim_catalog_text',
                        'locale' => 'en_US',
                        'scope' => null
                    ],
                    [
                        'attribute' => 'description',
                        'type' => 'pim_catalog_text',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce'
                    ],
                    [
                        'attribute' => 'description',
                        'type' => 'pim_catalog_text',
                        'locale' => 'fr_FR',
                        'scope' => 'ecommerce'
                    ],
                ]
            );


        $this->resolveEligibleValues([$sku, $name, $desc], ['ecommerce'], ['fr_FR', 'en_US'])->shouldReturn(
            [
                [
                    'attribute' => 'sku',
                    'type' => 'pim_catalog_identifier',
                    'locale' => null,
                    'scope' => null
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce'
                ],
            ]
        );
    }
}
