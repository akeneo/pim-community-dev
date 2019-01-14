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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\ProductSubscriptionRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributesMappingProviderInterface $attributesMappingProvider,
        ProductSubscriptionRepository $subscriptionRepository
    ): void {
        $this->beConstructedWith(
            $familyRepository,
            $attributeRepository,
            $attributesMappingProvider,
            $subscriptionRepository
        );
    }

    public function it_is_initializabel(): void
    {
        $this->shouldHaveType(SaveAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_family_does_not_exist(
        $familyRepository, $attributeRepository, AttributeInterface $attribute
    ): void {
        $attribute->getCode()->willReturn('tshirt_style');
        $attributeRepository->findBy(['code' => ['tshirt_style']])->willReturn([$attribute]);
        $familyRepository->findOneByIdentifier('router')->willReturn(null);

        $command = new SaveAttributesMappingByFamilyCommand('router', [
            'color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'tshirt_style',
            ],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_all_attributes_are_unknown($attributeRepository): void
    {
        $command = new SaveAttributesMappingByFamilyCommand('router', [
            'color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'tshirt_style',
            ],
        ]);

        $attributeRepository->findBy(['code' => ['tshirt_style']])->willReturn([]);

        $this->shouldThrow(AttributeMappingException::onlyUnknownMappedAttributes())->during('handle', [$command]);
    }

    public function it_saves_only_existing_attributes(
        $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        AttributeInterface $memoryAttribute
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
            'weight' => [
                'franklinAttribute' => [
                    'label' => 'Weight',
                    'type' => 'metric',
                ],
                'attribute' => 'product_weight',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $memoryAttribute->getCode()->willReturn('random_access_memory');
        $attributeRepository
            ->findBy(['code' => ['random_access_memory', 'product_weight']])
            ->willReturn([$memoryAttribute]);

        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(false);

        $attributeRepository->findOneByIdentifier('product_weight')->willReturn(null);

        $expectedAttribute = new AttributeMapping('memory', 'metric', 'random_access_memory');
        $expectedAttribute->setAttribute($memoryAttribute->getWrappedObject());
        $attributesMappingProvider
            ->saveAttributesMapping('router', [$expectedAttribute])
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_mapping_type_is_invalid(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand('router', [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
        ]);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attribute->getCode()->willReturn('random_access_memory');
        $attributeRepository
            ->findBy(['code' => ['random_access_memory']])
            ->willReturn([$attribute]);

        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::DATE);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_fills_attribute_and_calls_data_provider(
        $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        AttributeInterface $memoryAttribute,
        AttributeInterface $weightAttribute
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
            'weight' => [
                'franklinAttribute' => [
                    'label' => 'Weight',
                    'type' => 'metric',
                ],
                'attribute' => 'product_weight',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $memoryAttribute->getCode()->willReturn('random_access_memory');
        $weightAttribute->getCode()->willReturn('product_weight');

        $attributeRepository
            ->findBy(['code' => ['random_access_memory', 'product_weight']])
            ->willReturn([$memoryAttribute, $weightAttribute]);

        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(false);

        $attributeRepository->findOneByIdentifier('product_weight')->willReturn($weightAttribute);
        $weightAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $weightAttribute->isLocalizable()->willReturn(false);
        $weightAttribute->isScopable()->willReturn(false);
        $weightAttribute->isLocaleSpecific()->willReturn(false);

        $attributesMappingProvider
            ->saveAttributesMapping('router', $command->getAttributesMapping())
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_an_attribute_is_localizable(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $memoryAttribute
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $attributeRepository->findBy(['code' => ['random_access_memory']])->willReturn([$memoryAttribute]);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(true);
        $memoryAttribute->getCode()->willReturn('random_access_memory');

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_is_scopable(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $memoryAttribute
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeRepository->findBy(['code' => ['random_access_memory']])->willReturn([$memoryAttribute]);

        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(true);
        $memoryAttribute->getCode()->willReturn('random_access_memory');

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_is_locale_specific(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $memoryAttribute
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeRepository->findBy(['code' => ['random_access_memory']])->willReturn([$memoryAttribute]);
        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(true);
        $memoryAttribute->getCode()->willReturn('random_access_memory');

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }
}
