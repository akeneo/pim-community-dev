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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingWebService;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ): void {
        $this->beConstructedWith(
            $attributeRepository,
            $identifiersMappingRepository,
            $identifiersMappingProvider,
            $subscriptionRepository
        );
    }

    public function it_is_an_update_identifiers_mapping_handler(): void
    {
        $this->shouldHaveType(SaveIdentifiersMappingHandler::class);
    }

    public function it_throws_an_exception_if_an_attribute_does_not_exist(
        $identifiersMappingProvider,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeInterface $model
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

    public function it_saves_the_identifiers_mapping(
        $identifiersMappingProvider,
        $subscriptionRepository,
        $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $id
    ): void {
        $command = new SaveIdentifiersMappingCommand(
            [
                'brand' => 'manufacturer',
                'mpn' => 'model',
                'upc' => 'ean',
                'asin' => 'id',
            ]
        );

        $attributeRepository->findOneByIdentifier(Argument::any())->shouldBeCalledTimes(4);
        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $attributeRepository->findOneByIdentifier('id')->willReturn($id);

        $manufacturer->getType()->willReturn('pim_catalog_text');
        $manufacturer->isLocalizable()->willReturn(false);
        $manufacturer->isScopable()->willReturn(false);
        $manufacturer->isLocaleSpecific()->willReturn(false);

        $model->getType()->willReturn('pim_catalog_text');
        $model->isLocalizable()->willReturn(false);
        $model->isScopable()->willReturn(false);
        $model->isLocaleSpecific()->willReturn(false);

        $ean->getType()->willReturn('pim_catalog_text');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isLocaleSpecific()->willReturn(false);

        $id->getType()->willReturn('pim_catalog_text');
        $id->isLocalizable()->willReturn(false);
        $id->isScopable()->willReturn(false);
        $id->isLocaleSpecific()->willReturn(false);

        $identifiersMapping = new IdentifiersMapping();
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping
            ->map('brand', $manufacturer->getWrappedObject())
            ->map('mpn', $model->getWrappedObject())
            ->map('upc', $ean->getWrappedObject())
            ->map('asin', $id->getWrappedObject())
        ;

        $identifiersMappingProvider->saveIdentifiersMapping($identifiersMapping)->shouldBeCalled();
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();
        $subscriptionRepository->emptySuggestedData()->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_with_invalid_attribute_type(
        $identifiersMappingProvider,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $model
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
        $identifiersMappingProvider,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeInterface $manufacturer,
        AttributeInterface $ean
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
        $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingWebService $identifiersMappingWebService,
        AttributeInterface $model,
        AttributeInterface $ean
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
        $identifiersMappingWebService->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_localizable(
        $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingWebService $identifiersMappingWebService,
        AttributeInterface $attrEan
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
        $attrEan->getCode()->willReturn('ean');
        $attrEan->getType()->willReturn('pim_catalog_text');
        $attrEan->isLocalizable()->willReturn(true);
        $attrEan->isScopable()->willReturn(false);
        $attrEan->isLocaleSpecific()->willReturn(false);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->save(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::localizableAttributeNotAllowed('ean'))
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_scopable(
        $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingWebService $identifiersMappingWebService,
        AttributeInterface $attrAsin
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
        $attrAsin->getCode()->willReturn('pim_asin');
        $attrAsin->getType()->willReturn('pim_catalog_text');
        $attrAsin->isLocalizable()->willReturn(false);
        $attrAsin->isScopable()->willReturn(true);
        $attrAsin->isLocaleSpecific()->willReturn(false);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->save(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::scopableAttributeNotAllowed('pim_asin'))
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mapped_attribute_is_locale_specific(
        $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingWebService $identifiersMappingWebService,
        AttributeInterface $attrAsin
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
        $attrAsin->getCode()->willReturn('pim_asin');
        $attrAsin->getType()->willReturn('pim_catalog_text');
        $attrAsin->isLocalizable()->willReturn(false);
        $attrAsin->isScopable()->willReturn(false);
        $attrAsin->isLocaleSpecific()->willReturn(true);

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->save(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(InvalidMappingException::localeSpecificAttributeNotAllowed('pim_asin'))
            ->during('handle', [$command]);
    }
}
