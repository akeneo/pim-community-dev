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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
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
        $identifiersMappingProvider,
        Attribute $model
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'mpn' => 'model',
                'upc' => null,
                'asin' => null,
                'brand' => 'attributeNotFound',
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
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
        $identifyProductsToResubscribe,
        Attribute $manufacturer,
        Attribute $model,
        Attribute $ean,
        Attribute $id
    ): void {
        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($id);

        $manufacturer->getCode()->willReturn(new AttributeCode('manufacturer'));
        $manufacturer->getType()->willReturn('pim_catalog_text');
        $manufacturer->isLocalizable()->willReturn(false);
        $manufacturer->isScopable()->willReturn(false);
        $manufacturer->isLocaleSpecific()->willReturn(false);

        $model->getCode()->willReturn(new AttributeCode('model'));
        $model->getType()->willReturn('pim_catalog_text');
        $model->isLocalizable()->willReturn(false);
        $model->isScopable()->willReturn(false);
        $model->isLocaleSpecific()->willReturn(false);

        $ean->getCode()->willReturn(new AttributeCode('ean'));
        $ean->getType()->willReturn('pim_catalog_text');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isLocaleSpecific()->willReturn(false);

        $id->getCode()->willReturn(new AttributeCode('sku'));
        $id->getType()->willReturn('pim_catalog_text');
        $id->isLocalizable()->willReturn(false);
        $id->isScopable()->willReturn(false);
        $id->isLocaleSpecific()->willReturn(false);

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
        $identifyProductsToResubscribe,
        Attribute $sku,
        Attribute $asin
    ): void {
        $sku->getCode()->willReturn(new AttributeCode('sku'));
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->isLocaleSpecific()->willReturn(false);

        $asin->getCode()->willReturn(new AttributeCode('asin'));
        $asin->getType()->willReturn('pim_catalog_text');
        $asin->isLocalizable()->willReturn(false);
        $asin->isScopable()->willReturn(false);
        $asin->isLocaleSpecific()->willReturn(false);

        $attributeRepository->findOneByIdentifier('asin')->willReturn($asin);

        $identifiersMapping = new IdentifiersMapping(
            [
                'asin' => $asin->getWrappedObject(),
                'upc' => $sku->getWrappedObject(),
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
        $identifiersMappingProvider,
        Attribute $model
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'mpn' => 'model',
                'upc' => null,
                'asin' => null,
                'brand' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $model->getType()->willReturn('unknown_attribute_type');
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_brand_is_saved_without_mpn(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        Attribute $manufacturer,
        Attribute $ean
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => 'manufacturer',
                'mpn' => null,
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $manufacturer->getType()->willReturn('pim_catalog_text');
        $manufacturer->isLocalizable()->willReturn(false);
        $manufacturer->isScopable()->willReturn(false);
        $manufacturer->isLocaleSpecific()->willReturn(false);

        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $ean->getType()->willReturn('pim_catalog_text');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isLocaleSpecific()->willReturn(false);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mpn_is_saved_without_brand(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        Attribute $model,
        Attribute $ean
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => 'model',
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $model->getType()->willReturn('pim_catalog_text');
        $model->isLocalizable()->willReturn(false);
        $model->isScopable()->willReturn(false);
        $model->isLocaleSpecific()->willReturn(false);

        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $ean->getType()->willReturn('pim_catalog_text');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isLocaleSpecific()->willReturn(false);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_localizable(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        Attribute $attrEan
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => null,
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('ean')->willReturn($attrEan);
        $attrEan->getCode()->willReturn(new AttributeCode('ean'));
        $attrEan->getType()->willReturn('pim_catalog_text');
        $attrEan->isLocalizable()->willReturn(true);
        $attrEan->isScopable()->willReturn(false);
        $attrEan->isLocaleSpecific()->willReturn(false);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::localizableAttributeNotAllowed('ean'))
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_scopable(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        Attribute $attrAsin
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => null,
                'upc' => null,
                'asin' => 'pim_asin',
            ]
        );

        $attributeRepository->findOneByIdentifier('pim_asin')->willReturn($attrAsin);
        $attrAsin->getCode()->willReturn(new AttributeCode('pim_asin'));
        $attrAsin->getType()->willReturn('pim_catalog_text');
        $attrAsin->isLocalizable()->willReturn(false);
        $attrAsin->isScopable()->willReturn(true);
        $attrAsin->isLocaleSpecific()->willReturn(false);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::scopableAttributeNotAllowed('pim_asin'))
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_locale_specific(
        $attributeRepository,
        $identifiersMappingRepository,
        $identifiersMappingProvider,
        Attribute $attrAsin
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => null,
                'upc' => null,
                'asin' => 'pim_asin',
            ]
        );

        $attributeRepository->findOneByIdentifier('pim_asin')->willReturn($attrAsin);
        $attrAsin->getCode()->willReturn(new AttributeCode('pim_asin'));
        $attrAsin->getType()->willReturn('pim_catalog_text');
        $attrAsin->isLocalizable()->willReturn(false);
        $attrAsin->isScopable()->willReturn(false);
        $attrAsin->isLocaleSpecific()->willReturn(true);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->saveIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::localeSpecificAttributeNotAllowed('pim_asin'))
            ->during('handle', [$command]);
    }
}
