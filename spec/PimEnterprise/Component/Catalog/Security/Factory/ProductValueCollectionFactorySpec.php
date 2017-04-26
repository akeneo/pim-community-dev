<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Factory;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Factory\ProductValueCollectionFactoryInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\Catalog\Security\Factory\ValueCollectionFactory;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueCollectionFactoryInterface $valueCollectionFactory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        LoggerInterface $logger,
        CachedObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $valueCollectionFactory,
            $tokenStorage,
            $authorizationChecker,
            $logger,
            $attributeRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionFactory::class);
    }

    function it_is_a_product_value_collection_factory()
    {
        $this->shouldImplement(ProductValueCollectionFactoryInterface::class);
    }

    function it_filters_not_granted_attribute(
        $tokenStorage,
        $valueCollectionFactory,
        $authorizationChecker,
        TokenInterface $token,
        ProductValueCollection $productValueCollection,
        ProductValueInterface $productValueName,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attributeDescription,
        AttributeInterface $attributeName
    ) {
        $productValues = [
            'a_name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ],
            'a_description' => [
                'ecommerce' => [
                    'fr_FR' => 'bar'
                ]
            ]
        ];

        $tokenStorage->getToken()->willReturn($token);

        $productValueCollection->add($productValueName)->willReturn(true);
        $productValueCollection->count()->willReturn(1);

        $attributeRepository->findOneByIdentifier('a_name')->willReturn($attributeName);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeName)->willReturn(true);
        $attributeRepository->findOneByIdentifier('a_description')->willReturn($attributeDescription);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeDescription)->willReturn(false);

        $productValuesFiltered = $productValues;
        unset($productValuesFiltered['a_description']);

        $valueCollectionFactory->createFromStorageFormat($productValuesFiltered)->willReturn($productValueCollection);

        $actualValues = $this->createFromStorageFormat($productValues);

        $actualValues->shouldReturn($productValueCollection);
        $actualValues->shouldHaveCount(1);
    }

    function it_returns_all_product_values_if_there_is_no_token(
        $tokenStorage,
        $valueCollectionFactory,
        ProductValueCollection $productValueCollection,
        ProductValueInterface $productValueName,
        ProductValueInterface $productValueDescription
    ) {
        $productValues = [
            'a_name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ],
            'a_description' => [
                'ecommerce' => [
                    'fr_FR' => 'bar'
                ]
            ]
        ];

        $tokenStorage->getToken()->willReturn(null);

        $productValueCollection->add($productValueName)->willReturn(true);
        $productValueCollection->add($productValueDescription)->willReturn(true);
        $productValueCollection->count()->willReturn(2);
        $valueCollectionFactory->createFromStorageFormat($productValues)->willReturn($productValueCollection);

        $actualValues = $this->createFromStorageFormat($productValues);

        $actualValues->shouldReturn($productValueCollection);
        $actualValues->shouldHaveCount(2);
    }

    function it_skips_unknown_attributes_when_creating_a_values_collection_from_the_storage_format(
        $tokenStorage,
        $valueCollectionFactory,
        $attributeRepository,
        $logger,
        TokenInterface $token,
        ProductValueFactory $valueFactory,
        ProductValueCollection $productValueCollection
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $attributeRepository->findOneByIdentifier('attribute_that_does_not_exists')->willReturn(null);

        $valueFactory->create(Argument::cetera())->shouldNotBeCalled();
        $logger->warning('Tried to load a product value with the attribute "attribute_that_does_not_exists" that does not exist.');
        $valueCollectionFactory->createFromStorageFormat([])->willReturn($productValueCollection);

        $actualValues = $this->createFromStorageFormat([
            'attribute_that_does_not_exists' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ]
        ]);

        $actualValues->shouldReturnAnInstanceOf($productValueCollection);
        $actualValues->shouldHaveCount(0);
    }
}
