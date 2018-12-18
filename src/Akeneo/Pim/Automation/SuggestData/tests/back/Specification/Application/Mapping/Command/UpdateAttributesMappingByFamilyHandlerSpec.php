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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine\ProductSubscriptionRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
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
        $this->shouldHaveType(UpdateAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_family_does_not_exist($familyRepository): void
    {
        $familyRepository->findOneByIdentifier('router')->willReturn(null);

        $command = new UpdateAttributesMappingByFamilyCommand('router', []);
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_an_attribute_does_not_exist(
        $familyRepository,
        $attributeRepository
    ): void {
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::type(FamilyInterface::class));
        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn(null);

        $command = new UpdateAttributesMappingByFamilyCommand('router', [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
        ]);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_mapping_type_is_invalid(
        $familyRepository,
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $command = new UpdateAttributesMappingByFamilyCommand('router', [
            'memory' => [
                'franklinAttribute' => [
                    'label' => 'Memory',
                    'type' => 'metric',
                ],
                'attribute' => 'random_access_memory',
            ],
        ]);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

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
        $command = new UpdateAttributesMappingByFamilyCommand('router', $attributeMapping);

        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeRepository->findOneByIdentifier('random_access_memory')->willReturn($memoryAttribute);
        $memoryAttribute->getType()->willReturn(AttributeTypes::METRIC);

        $attributeRepository->findOneByIdentifier('product_weight')->willReturn($weightAttribute);
        $weightAttribute->getType()->willReturn(AttributeTypes::TEXT);

        $attributesMappingProvider
            ->updateAttributesMapping('router', $command->getAttributesMapping())
            ->shouldBeCalled();

        $subscriptionRepository
            ->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode())
            ->shouldBeCalled();

        $this->handle($command);
    }
}
