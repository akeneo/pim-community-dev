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
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
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
class UpdateIdentifiersMappingHandlerSpec extends ObjectBehavior
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
        $this->shouldHaveType(UpdateIdentifiersMappingHandler::class);
    }

    public function it_throws_an_exception_if_an_attribute_does_not_exist(
        $identifiersMappingProvider,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeInterface $model
    ): void {
        $command = new UpdateIdentifiersMappingCommand(
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
        $identifiersMappingProvider->updateIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            InvalidMappingException::attributeNotFound('attributeNotFound', UpdateIdentifiersMappingHandler::class)
        )->during('handle', [$command]);
    }

    public function it_saves_the_identifiers_mapping(
        $identifiersMappingProvider,
        $subscriptionRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $id
    ): void {
        $command = new UpdateIdentifiersMappingCommand(
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
        $model->getType()->willReturn('pim_catalog_text');
        $ean->getType()->willReturn('pim_catalog_text');
        $id->getType()->willReturn('pim_catalog_text');

        $identifiersMapping = new IdentifiersMapping(
            [
                'brand' => $manufacturer->getWrappedObject(),
                'mpn' => $model->getWrappedObject(),
                'upc' => $ean->getWrappedObject(),
                'asin' => $id->getWrappedObject(),
            ]
        );
        $identifiersMappingProvider->updateIdentifiersMapping($identifiersMapping)->shouldBeCalled();
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();
        $subscriptionRepository->emptySuggestedData()->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_with_invalid_attribute_type(
        $identifiersMappingProvider,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $model
    ): void {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'mpn' => 'model',
                'upc' => null,
                'asin' => null,
                'brand' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $model->getType()->willReturn('unknown_attribute_type');
        $identifiersMappingProvider->updateIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_brand_is_saved_without_mpn(
        $identifiersMappingProvider,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeInterface $manufacturer,
        AttributeInterface $ean
    ): void {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'brand' => 'manufacturer',
                'mpn' => null,
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $manufacturer->getType()->willReturn('pim_catalog_text');
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $ean->getType()->willReturn('pim_catalog_text');
        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingProvider->updateIdentifiersMapping(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mpn_is_saved_without_brand(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingWebService $identifiersMappingWebService,
        AttributeInterface $model,
        AttributeInterface $ean,
        $identifiersMappingRepository
    ): void {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn' => 'model',
                'upc' => 'ean',
                'asin' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $model->getType()->willReturn('pim_catalog_text');
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $ean->getType()->willReturn('pim_catalog_text');
        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->update(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }
}
