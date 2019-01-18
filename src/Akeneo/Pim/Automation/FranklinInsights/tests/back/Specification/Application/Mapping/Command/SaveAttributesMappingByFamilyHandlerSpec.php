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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\ProductSubscriptionRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

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
        $familyRepository,
        $attributeRepository,
        AttributeInterface $attribute
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

    public function it_throws_an_exception_if_all_attributes_are_unknown(
        $familyRepository,
        $attributeRepository,
        FamilyInterface $family
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand('router', [
            'color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'tshirt_style',
            ],
        ]);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['tshirt_style']);
        $attributeRepository->findBy(['code' => ['tshirt_style']])->willReturn([]);

        $this->shouldThrow(AttributeMappingException::onlyUnknownMappedAttributes())->during('handle', [$command]);
    }

    public function it_maps_to_null_when_attribute_does_not_exist(
        $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        AttributeInterface $memoryAttribute,
        FamilyInterface $family
    ): void {
        $attributesMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
            'weight' => [
                'franklinAttribute' => [
                    'label' => 'Weight',
                    'type' => 'metric',
                ],
                'attribute' => 'product_weight',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributesMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'ram', 'product_weight']);

        $attributeRepository
            ->findBy(['code' => ['ram', 'product_weight']])
            ->willReturn([$memoryAttribute]);

        $memoryAttribute->getCode()->willReturn('ram');
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(false);

        $expectedAttributesMapping = new AttributesMapping('router');
        $expectedAttributesMapping->map('memory', 'metric', $memoryAttribute->getWrappedObject());
        $expectedAttributesMapping->map('weight', 'metric', null);

        $attributesMappingProvider
            ->saveAttributesMapping('router', $expectedAttributesMapping->mapping())
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_maps_to_null_when_the_attribute_is_not_in_the_family(
        $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        FamilyInterface $family,
        AttributeInterface $memoryAttribute
    ): void {
        $attributesMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
            'weight' => [
                'franklinAttribute' => [
                    'label' => 'Weight',
                    'type' => 'metric',
                ],
                'attribute' => 'product_weight',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributesMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'ram']);

        $attributeRepository
            ->findBy(['code' => ['ram']])
            ->willReturn([$memoryAttribute]);

        $memoryAttribute->getCode()->willReturn('ram');
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(false);

        $expectedAttributesMapping = new AttributesMapping('router');
        $expectedAttributesMapping->map('memory', 'metric', $memoryAttribute->getWrappedObject());
        $expectedAttributesMapping->map('weight', 'metric', null);

        $attributesMappingProvider
            ->saveAttributesMapping('router', $expectedAttributesMapping->mapping())
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_mapping_type_is_invalid(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $attribute,
        FamilyInterface $family
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand('router', [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Release date',
                    'type' => 'text',
                ],
                'attribute' => 'release_date',
            ],
        ]);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'release_date']);

        $attributeRepository
            ->findBy(['code' => ['release_date']])
            ->willReturn([$attribute]);

        $attribute->getCode()->willReturn('release_date');
        $attribute->getType()->willReturn(AttributeTypes::DATE);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_successfully_save_attributes_mapping(
        $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        AttributeInterface $memoryAttribute,
        AttributeInterface $weightAttribute,
        FamilyInterface $family
    ): void {
        $attributesMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
            'weight' => [
                'franklinAttribute' => [
                    'label' => 'Weight',
                    'type' => 'metric',
                ],
                'attribute' => 'product_weight',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributesMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'ram', 'product_weight']);

        $attributeRepository
            ->findBy(['code' => ['ram', 'product_weight']])
            ->willReturn([$memoryAttribute, $weightAttribute]);

        $memoryAttribute->getCode()->willReturn('ram');
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(false);

        $weightAttribute->getCode()->willReturn('product_weight');
        $weightAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $weightAttribute->isLocalizable()->willReturn(false);
        $weightAttribute->isScopable()->willReturn(false);
        $weightAttribute->isLocaleSpecific()->willReturn(false);

        $attributesMapping = new AttributesMapping('router');
        $attributesMapping->map('memory', 'metric', $memoryAttribute->getWrappedObject());
        $attributesMapping->map('weight', 'metric', $weightAttribute->getWrappedObject());

        $attributesMappingProvider
            ->saveAttributesMapping('router', $attributesMapping->mapping())
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_an_attribute_is_localizable(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $memoryAttribute,
        FamilyInterface $family
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand('router', [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
        ]);

        $attributeRepository->findBy(['code' => ['ram']])->willReturn([$memoryAttribute]);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'ram']);

        $memoryAttribute->getCode()->willReturn('ram');
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(true);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_is_scopable(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $memoryAttribute,
        FamilyInterface $family
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'ram']);

        $attributeRepository->findBy(['code' => ['ram']])->willReturn([$memoryAttribute]);

        $attributeRepository->findOneByIdentifier('ram')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(true);
        $memoryAttribute->getCode()->willReturn('ram');

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_is_locale_specific(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $memoryAttribute,
        FamilyInterface $family
    ): void {
        $attributeMapping = [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
        ];
        $command = new SaveAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['sku', 'ram']);

        $attributeRepository->findBy(['code' => ['ram']])->willReturn([$memoryAttribute]);
        $attributeRepository->findOneByIdentifier('ram')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $memoryAttribute->isLocalizable()->willReturn(false);
        $memoryAttribute->isScopable()->willReturn(false);
        $memoryAttribute->isLocaleSpecific()->willReturn(true);
        $memoryAttribute->getCode()->willReturn('ram');

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }
}
