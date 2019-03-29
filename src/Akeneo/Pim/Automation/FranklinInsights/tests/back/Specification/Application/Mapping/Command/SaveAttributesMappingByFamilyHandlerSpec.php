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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\ProductSubscriptionRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
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
        ProductSubscriptionRepository $subscriptionRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
    ): void {
        $this->beConstructedWith(
            $familyRepository,
            $attributeRepository,
            $attributesMappingProvider,
            $subscriptionRepository,
            $selectFamilyAttributeCodesQuery
        );
    }

    public function it_is_initializabel(): void
    {
        $this->shouldHaveType(SaveAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_family_does_not_exist(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository
    ): void {
        $attributeRepository->findByCodes(['tshirt_style'])->willReturn([AttributeBuilder::fromCode('tshirt_style')]);
        $familyRepository->exist(new FamilyCode('router'))->willReturn(false);

        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), [
            'color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'tshirt_style',
            ],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_all_attributes_are_unknown(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), [
            'color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'tshirt_style',
            ],
        ]);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['tshirt_style']);

        $attributeRepository->findByCodes(['tshirt_style'])->willReturn([]);

        $this->shouldThrow(AttributeMappingException::emptyAttributesMapping())->during('handle', [$command]);
    }

    public function it_maps_to_null_when_attribute_does_not_exist(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
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
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), $attributesMapping);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'ram', 'product_weight']);

        $memoryAttribute = AttributeBuilder::fromCode('ram');

        $attributeRepository
            ->findByCodes(['ram', 'product_weight'])
            ->willReturn([$memoryAttribute]);

        $expectedAttributesMapping = new AttributesMapping($familyCode);
        $expectedAttributesMapping->map('memory', 'metric', $memoryAttribute);
        $expectedAttributesMapping->map('weight', 'metric', null);

        $attributesMappingProvider
            ->saveAttributesMapping($familyCode, $expectedAttributesMapping)
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_maps_to_null_when_the_attribute_is_not_in_the_family(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
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
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), $attributesMapping);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'ram']);

        $memoryAttribute = AttributeBuilder::fromCode('ram');

        $attributeRepository
            ->findByCodes(['ram'])
            ->willReturn([$memoryAttribute]);

        $expectedAttributesMapping = new AttributesMapping($familyCode);
        $expectedAttributesMapping->map('memory', 'metric', $memoryAttribute);
        $expectedAttributesMapping->map('weight', 'metric', null);

        $attributesMappingProvider
            ->saveAttributesMapping($familyCode, $expectedAttributesMapping)
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_mapping_type_is_invalid(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Release date',
                    'type' => 'text',
                ],
                'attribute' => 'release_date',
            ],
        ]);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'release_date']);

        $attribute = (new AttributeBuilder())->withCode('release_date')->withType(AttributeTypes::DATE)->build();

        $attributeRepository
            ->findByCodes(['release_date'])
            ->willReturn([$attribute]);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_successfully_save_attributes_mapping(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        $attributesMappingProvider,
        $subscriptionRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
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
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), $attributesMapping);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'ram', 'product_weight']);

        $memoryAttribute = (new AttributeBuilder())->withCode('ram')->withType(AttributeTypes::METRIC)->build();
        $weightAttribute = (new AttributeBuilder())->withCode('product_weight')->withType(AttributeTypes::TEXT)->build();

        $attributeRepository
            ->findByCodes(['ram', 'product_weight'])
            ->willReturn([$memoryAttribute, $weightAttribute]);

        $attributesMapping = new AttributesMapping($familyCode);
        $attributesMapping->map('memory', 'metric', $memoryAttribute);
        $attributesMapping->map('weight', 'metric', $weightAttribute);

        $attributesMappingProvider
            ->saveAttributesMapping($familyCode, $attributesMapping)
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_an_attribute_is_localizable(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
    ): void {
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'ram',
            ],
        ]);

        $memoryAttribute = (new AttributeBuilder())->withCode('ram')->withType(AttributeTypes::METRIC)->isLocalizable()->build();

        $attributeRepository->findByCodes(['ram'])->willReturn([$memoryAttribute]);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'ram']);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_is_scopable(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
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
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), $attributeMapping);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'ram']);

        $memoryAttribute = (new AttributeBuilder())->withCode('ram')->withType(AttributeTypes::METRIC)->isScopable()->build();

        $attributeRepository->findByCodes(['ram'])->willReturn([$memoryAttribute]);
        $attributeRepository->findOneByIdentifier('ram')->willReturn($memoryAttribute);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_is_locale_specific(
        FamilyRepositoryInterface $familyRepository,
        $attributeRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
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
        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode('router'), $attributeMapping);

        $familyCode = new FamilyCode('router');
        $familyRepository->exist($familyCode)->willReturn(true);
        $selectFamilyAttributeCodesQuery->execute($familyCode)->willReturn(['sku', 'ram']);

        $memoryAttribute = (new AttributeBuilder())->withCode('ram')->withType(AttributeTypes::METRIC)->isLocaleSpecific()->build();

        $attributeRepository->findByCodes(['ram'])->willReturn([$memoryAttribute]);
        $attributeRepository->findOneByIdentifier('ram')->willReturn($memoryAttribute);

        $this->shouldThrow(AttributeMappingException::class)->during('handle', [$command]);
    }
}
