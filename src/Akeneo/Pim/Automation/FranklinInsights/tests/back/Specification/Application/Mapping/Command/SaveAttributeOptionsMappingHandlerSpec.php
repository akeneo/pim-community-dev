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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Exception\AttributeOptionsMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveAttributeOptionsMappingHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeOptionsMappingProviderInterface $mappingProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ): void {
        $this->beConstructedWith(
            $mappingProvider,
            $familyRepository,
            $attributeRepository,
            $attributeOptionRepository
        );
    }

    public function it_is_initializabel(): void
    {
        $this->shouldHaveType(SaveAttributeOptionsMappingHandler::class);
    }

    public function it_saves_attribute_options(
        $familyRepository,
        $attributeRepository,
        $attributeOptionRepository,
        $mappingProvider,
        FamilyInterface $family,
        AttributeInterface $attribute
    ): void {
        $familyCode = new FamilyCode('foo');
        $attributeCode = new AttributeCode('burger');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $familyRepository->findOneByIdentifier($familyCode)->willReturn($family);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attributeOptionRepository
            ->findCodesByIdentifiers((string) $attributeCode, ['color1', 'color2'])
            ->willReturn(['color1', 'color2']);

        $writeOptionsMapping = new AttributeOptionsMapping();
        $writeOptionsMapping->addAttributeOption(new AttributeOption('color_1', 'Color 1', 'color1'));
        $writeOptionsMapping->addAttributeOption(new AttributeOption('color_2', 'Color 2', 'color2'));
        $mappingProvider
            ->saveAttributeOptionsMapping($familyCode, $franklinAttributeId, $writeOptionsMapping)
            ->shouldBeCalled();

        $command = new SaveAttributeOptionsMappingCommand(
            $familyCode,
            $attributeCode,
            $franklinAttributeId,
            $this->buildMapping()
        );

        $this->handle($command);
    }

    public function it_ignores_the_option_if_it_does_not_exist(
        $familyRepository,
        $attributeRepository,
        $attributeOptionRepository,
        $mappingProvider,
        FamilyInterface $family,
        AttributeInterface $attribute
    ): void {
        $familyCode = new FamilyCode('foo');
        $attributeCode = new AttributeCode('burger');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $familyRepository->findOneByIdentifier($familyCode)->willReturn($family);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attributeOptionRepository
            ->findCodesByIdentifiers((string) $attributeCode, ['color1', 'color2'])
            ->willReturn(['color1']);

        $writeOptionsMapping = new AttributeOptionsMapping();
        $writeOptionsMapping->addAttributeOption(new AttributeOption('color_1', 'Color 1', 'color1'));
        $writeOptionsMapping->addAttributeOption(new AttributeOption('color_2', 'Color 2', null));
        $mappingProvider
            ->saveAttributeOptionsMapping($familyCode, $franklinAttributeId, $writeOptionsMapping)
            ->shouldBeCalled();

        $command = new SaveAttributeOptionsMappingCommand(
            $familyCode,
            $attributeCode,
            $franklinAttributeId,
            $this->buildMapping()
        );

        $this->handle($command);
    }

    public function it_throws_an_empty_mapping_exception_when_no_options_are_mapped(
        $familyRepository,
        $attributeRepository,
        $attributeOptionRepository,
        $mappingProvider,
        FamilyInterface $family,
        AttributeInterface $attribute
    ): void {
        $familyCode = new FamilyCode('foo');
        $attributeCode = new AttributeCode('burger');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $familyRepository->findOneByIdentifier($familyCode)->willReturn($family);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attributeOptionRepository
            ->findCodesByIdentifiers((string) $attributeCode, ['color1', 'color2'])
            ->willReturn([]);

        $mappingProvider
            ->saveAttributeOptionsMapping(
                $familyCode,
                $franklinAttributeId,
                Argument::type(AttributeOptionsMapping::class)
            )
            ->shouldNotBeCalled();

        $command = new SaveAttributeOptionsMappingCommand(
            $familyCode,
            $attributeCode,
            $franklinAttributeId,
            $this->buildMapping()
        );

        $this
            ->shouldThrow(AttributeOptionsMappingException::emptyAttributeOptionsMapping())
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_the_family_does_not_exist($familyRepository): void
    {
        $familyCode = new FamilyCode('foo');

        $command = new SaveAttributeOptionsMappingCommand(
            $familyCode,
            new AttributeCode('foo'),
            new FranklinAttributeId('bar'),
            $this->buildMapping()
        );

        $familyRepository->findOneByIdentifier($familyCode)->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_the_attribute_does_not_exist(
        $familyRepository,
        $attributeRepository,
        FamilyInterface $family
    ): void {
        $familyCode = new FamilyCode('foo');
        $attributeCode = new AttributeCode('burger');

        $command = new SaveAttributeOptionsMappingCommand(
            $familyCode,
            $attributeCode,
            new FranklinAttributeId('bar'),
            $this->buildMapping()
        );

        $familyRepository->findOneByIdentifier($familyCode)->willReturn($family);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    private function buildMapping()
    {
        return new AttributeOptions([
            'color_1' => [
                'franklinAttributeOptionCode' => [
                    'label' => 'Color 1',
                ],
                'catalogAttributeOptionCode' => 'color1',
                'status' => 0,
            ],
            'color_2' => [
                'franklinAttributeOptionCode' => [
                    'label' => 'Color 2',
                ],
                'catalogAttributeOptionCode' => 'color2',
                'status' => 1,
            ],
        ]);
    }
}
