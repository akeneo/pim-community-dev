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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidAttributeMappingTypeException;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidExternalAttributeTypeException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider
    ): void {
        $this->beConstructedWith($familyRepository, $attributeRepository, $dataProviderFactory);

        $dataProviderFactory->create()->willReturn($dataProvider);
    }

    public function it_is_initializabel(): void
    {
        $this->shouldHaveType(UpdateAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_family_does_not_exist(
        UpdateAttributesMappingByFamilyCommand $command,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $command->getFamilyCode()->willReturn('router');
        $familyRepository->findOneByIdentifier('router')->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_does_not_exist(
        UpdateAttributesMappingByFamilyCommand $command,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeMapping $attributeMapping
    ): void {
        $command->getFamilyCode()->willReturn('router');
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeCode = 'memory';
        $attributeMapping->getPimAttributeCode()->willReturn($attributeCode);

        $command->getAttributesMapping()->willReturn([$attributeMapping]);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_external_attribute_type_is_unknown(
        UpdateAttributesMappingByFamilyCommand $command,
        AttributeMapping $attributeMapping,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $command->getFamilyCode()->willReturn('router');
        $command->getAttributesMapping()->willReturn([$attributeMapping]);

        $attributeCode = 'memory';
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attributeMapping->getPimAttributeCode()->willReturn($attributeCode);

        $attributeMapping->getPimAiAttributeType()->willReturn('unknown_type');

        $this->shouldThrow(InvalidExternalAttributeTypeException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_mapping_type_is_invalid(
        UpdateAttributesMappingByFamilyCommand $command,
        AttributeMapping $attributeMapping,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $command->getFamilyCode()->willReturn('router');
        $command->getAttributesMapping()->willReturn([$attributeMapping]);

        $attributeCode = 'memory';
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attributeMapping->getPimAttributeCode()->willReturn($attributeCode);
        $attributeMapping->setAttribute($attribute)->shouldNotBeCalled();

        $attributeMapping->getPimAiAttributeType()->willReturn('multiselect');
        $attribute->getType()->willReturn('pim_catalog_metric');

        $this->shouldThrow(InvalidAttributeMappingTypeException::class)->during('handle', [$command]);
    }

    public function it_fills_attribute_and_calls_data_provider(
        UpdateAttributesMappingByFamilyCommand $command,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeMapping $attributeMapping,
        AttributeInterface $attribute,
        DataProviderInterface $dataProvider
    ): void {
        $command->getFamilyCode()->willReturn('router');
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeCode = 'memory';

        $attributeMapping->getPimAttributeCode()->willReturn($attributeCode);

        $command->getAttributesMapping()->willReturn([$attributeMapping]);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);

        $attributeMapping->getPimAiAttributeType()->willReturn('multiselect');
        $attribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeMapping->setAttribute($attribute)->shouldBeCalled();
        $dataProvider->updateAttributesMapping('router', [$attributeMapping])->shouldBeCalled();

        $this->handle($command);
    }
}
