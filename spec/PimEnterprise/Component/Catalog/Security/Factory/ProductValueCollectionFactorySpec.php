<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Factory;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueInterface;
use PimEnterprise\Component\Catalog\Security\Factory\ValueCollectionFactory;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueCollectionFactoryInterface $valueCollectionFactory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        LoggerInterface $logger,
        CachedObjectRepositoryInterface $attributeRepository,
        CachedObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith(
            $valueCollectionFactory,
            $tokenStorage,
            $authorizationChecker,
            $logger,
            $attributeRepository,
            $localeRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionFactory::class);
    }

    function it_is_a_product_value_collection_factory()
    {
        $this->shouldImplement(ValueCollectionFactoryInterface::class);
    }

    function it_filters_not_granted_attribute(
        $tokenStorage,
        $valueCollectionFactory,
        $authorizationChecker,
        $attributeRepository,
        $localeRepository,
        TokenInterface $token,
        ValueCollection $valueCollection,
        ValueInterface $valueName,
        AttributeInterface $attributeDescription,
        AttributeInterface $attributeName,
        LocaleInterface $localeFR,
        LocaleInterface $localeEN
    ) {
        $productValues = [
            'a_name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ],
            'a_description' => [
                'ecommerce' => [
                    'fr_FR' => 'bar',
                    'en_US' => 'bar'
                ],
            ],
            'a_number' => [
                '<all_channels>' => [
                    'fr_FR' => 'bar'
                ]
            ],
        ];

        $tokenStorage->getToken()->willReturn($token);

        $valueCollection->add($valueName)->willReturn(true);
        $valueCollection->count()->willReturn(1);

        $attributeRepository->findOneByIdentifier('a_name')->willReturn($attributeName);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeName)->willReturn(true);
        $attributeRepository->findOneByIdentifier('a_description')->willReturn($attributeDescription);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeDescription)->willReturn(false);
        $attributeRepository->findOneByIdentifier('a_number')->willReturn($attributeDescription);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeDescription)->willReturn(true);

        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFR);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeFR)->willReturn(false);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEN);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeEN)->willReturn(true);

        $productValuesFiltered = $productValues;
        unset($productValuesFiltered['a_description']['ecommerce']['fr_FR']);
        unset($productValuesFiltered['a_number']);

        $valueCollectionFactory->createFromStorageFormat($productValuesFiltered)->willReturn($valueCollection);

        $actualValues = $this->createFromStorageFormat($productValues);

        $actualValues->shouldReturn($valueCollection);
        $actualValues->shouldHaveCount(1);
    }

    function it_throws_an_expcetion_if_there_is_no_token($tokenStorage)
    {
        $tokenStorage->getToken()->willReturn(null);

        $this->shouldThrow(new \LogicException('The token cannot be null.'))->during('createFromStorageFormat', [[]]);
    }

    function it_skips_unknown_attributes_when_creating_a_values_collection_from_the_storage_format(
        $tokenStorage,
        $valueCollectionFactory,
        $attributeRepository,
        $logger,
        TokenInterface $token,
        ValueFactory $valueFactory,
        ValueCollection $valueCollection
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $attributeRepository->findOneByIdentifier('attribute_that_does_not_exists')->willReturn(null);

        $valueFactory->create(Argument::cetera())->shouldNotBeCalled();
        $logger->warning('Tried to load a product value with the attribute "attribute_that_does_not_exists" that does not exist.');
        $valueCollectionFactory->createFromStorageFormat([])->willReturn($valueCollection);

        $actualValues = $this->createFromStorageFormat([
            'attribute_that_does_not_exists' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ]
        ]);

        $actualValues->shouldReturnAnInstanceOf($valueCollection);
        $actualValues->shouldHaveCount(0);
    }
}
