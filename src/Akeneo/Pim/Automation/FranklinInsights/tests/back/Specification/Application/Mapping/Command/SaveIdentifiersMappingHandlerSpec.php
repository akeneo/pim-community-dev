<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\IdentifyProductsToResubscribeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveIdentifiersMappingHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingProviderInterface $identifiersMappingProvider,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        IdentifyProductsToResubscribeInterface $identifyProductsToResubscribe
    ): void {
        $this->beConstructedWith(
            $attributeRepository,
            $identifiersMappingRepository,
            $identifiersMappingProvider,
            $subscriptionRepository,
            $identifyProductsToResubscribe
        );
    }

    public function it_is_an_update_identifiers_mapping_handler(): void
    {
        $this->shouldHaveType(SaveIdentifiersMappingHandler::class);
    }

    public function it_throws_an_exception_if_an_attribute_does_not_exist(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'mpn' => 'model',
                'upc' => null,
                'asin' => null,
                'brand' => 'attributeNotFound',
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn(AttributeBuilder::fromCode('model'));
        $attributeRepository->findOneByIdentifier('attributeNotFound')->willReturn(null);
        $attributeRepository->findOneByIdentifier(null)->shouldNotBeCalled();

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$command]);
    }

    public function it_saves_a_new_identifiers_mapping(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        $subscriptionRepository,
        $identifyProductsToResubscribe
    ): void {
        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn(AttributeBuilder::fromCode('manufacturer'));
        $attributeRepository->findOneByIdentifier('model')->willReturn(AttributeBuilder::fromCode('model'));
        $attributeRepository->findOneByIdentifier('ean')->willReturn(AttributeBuilder::fromCode('ean'));
        $attributeRepository->findOneByIdentifier('sku')->willReturn(AttributeBuilder::fromCode('sku'));

        $identifiersMapping = new IdentifiersMapping([]);
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $identifiersMappingProvider->saveIdentifiersMapping($identifiersMapping)->shouldBeCalled();
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();
        $subscriptionRepository->emptySuggestedData()->shouldBeCalled();

        $identifyProductsToResubscribe->process(Argument::any())->shouldNotBeCalled();

        $this->handle(
            new SaveIdentifiersMappingCommand(
                [
                    'brand' => 'manufacturer',
                    'mpn' => 'model',
                    'upc' => 'ean',
                    'asin' => 'sku',
                ]
            )
        );
    }

    public function it_updates_an_existing_identifiers_mapping(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        $subscriptionRepository,
        $identifyProductsToResubscribe
    ): void {
        $attributeRepository->findOneByIdentifier('asin')->willReturn(AttributeBuilder::fromCode('asin'));

        $identifiersMapping = new IdentifiersMapping(
            [
                'asin' => 'asin',
                'upc' => 'sku',
            ]
        );
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $identifiersMappingProvider->saveIdentifiersMapping($identifiersMapping)->shouldBeCalled();
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();
        $subscriptionRepository->emptySuggestedData()->shouldBeCalled();

        $identifyProductsToResubscribe->process(['upc'])->shouldBeCalled();

        $this->handle(
            new SaveIdentifiersMappingCommand(
                [
                    'asin' => 'asin',
                    'upc' => null,
                    'brand' => null,
                    'mpn' => null,
                ]
            )
        );
    }

    public function it_throws_an_exception_with_invalid_attribute_type(
        $attributeRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'mpn' => 'model',
                'upc' => null,
                'asin' => null,
                'brand' => null,
            ]
        );

        $attribute = (new AttributeBuilder())->withCode('model')->withType('unknown_attribute_type')->build();
        $attributeRepository->findOneByIdentifier('model')->willReturn($attribute);
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_brand_is_saved_without_mpn(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => 'manufacturer',
                'mpn' => null,
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn(AttributeBuilder::fromCode('manufacturer'));

        $attributeRepository->findOneByIdentifier('ean')->willReturn(AttributeBuilder::fromCode('ean'));

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mpn_is_saved_without_brand(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => 'model',
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn(AttributeBuilder::fromCode('model'));

        $attributeRepository->findOneByIdentifier('ean')->willReturn(AttributeBuilder::fromCode('ean'));

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_localizable(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => null,
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attribute = (new AttributeBuilder())->withCode('ean')->isLocalizable()->build();
        $attributeRepository->findOneByIdentifier('ean')->willReturn($attribute);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::localizableAttributeNotAllowed('ean'))
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_scopable(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => null,
                'upc' => null,
                'asin' => 'pim_asin',
            ]
        );

        $attrAsin = (new AttributeBuilder())->withCode('pim_asin')->isScopable()->build();
        $attributeRepository->findOneByIdentifier('pim_asin')->willReturn($attrAsin);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::scopableAttributeNotAllowed('pim_asin'))
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_locale_specific(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => null,
                'upc' => null,
                'asin' => 'pim_asin',
            ]
        );

        $attrAsin = (new AttributeBuilder())->withCode('pim_asin')->isLocaleSpecific()->build();

        $attributeRepository->findOneByIdentifier('pim_asin')->willReturn($attrAsin);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::localeSpecificAttributeNotAllowed('pim_asin'))
            ->during('handle', [$command]);
    }
}
