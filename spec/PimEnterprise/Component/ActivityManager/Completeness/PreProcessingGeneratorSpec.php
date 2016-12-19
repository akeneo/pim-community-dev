<?php

namespace spec\PimEnterprise\Component\ActivityManager\Completeness;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Completeness\PreProcessingGeneratorInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\StructuredAttributeRepositoryInterface;

class PreProcessingGeneratorSpec extends ObjectBehavior
{
    function let(
        PreProcessingRepositoryInterface $preProcessingRepository,
        StructuredAttributeRepositoryInterface $familyRequirementRepository,
        StructuredAttributeRepositoryInterface $structuredAttributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->beConstructedWith(
            $preProcessingRepository,
            $familyRequirementRepository,
            $structuredAttributeRepository,
            $attributeGroupRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessingGeneratorInterface::class);
    }

    function it_generates_pre_processing_data_for_done_values(
        $preProcessingRepository,
        $familyRequirementRepository,
        $structuredAttributeRepository,
        $attributeGroupRepository,
        AttributeGroupInterface $marketing,
        AttributeGroupInterface $technical
    ) {
        $structuredAttributes = [
            'marketing' => [
                'sku',
                'name',
            ],
            'technical' => [
                'weight',
                'height',
            ],
        ];


        $mandatoryAttributes = [
            'marketing' => [
                'sku',
                'name',
            ],
            'technical' => [
                'weight',
                'height',
            ],
        ];

        $attributeGroupRepository->find('marketing')->willReturn($marketing);
        $attributeGroupRepository->find('marketing')->willReturn($technical);

        $marketing->getId()->willReturn(1);
        $technical->getId()->willReturn(2);

        $structuredAttributeRepository
            ->getStructuredAttributes(42, 'ecommerce', 'en_US')->willReturn($structuredAttributes);

        $familyRequirementRepository
            ->getStructuredAttributes($structuredAttributes['familyCode'], 'ecommerce', 'em_US')
            ->willReturn($mandatoryAttributes);

        $preProcessingRepository->addPreProcessingData(42, 1, 0, 1)->shouldBeCalled();
        $preProcessingRepository->addPreProcessingData(42, 2, 0, 1)->shouldBeCalled();

        $this->generate(42, 'ecommerce', 'en_US', 1, 3);
    }

    function it_generates_pre_processing_data_for_at_least_values(
        $preProcessingRepository,
        $familyRequirementRepository,
        $structuredAttributeRepository,
        $attributeGroupRepository,
        AttributeGroupInterface $marketing
    ) {
        $mandatoryAttributes = [
            'marketing' => [
                'sku',
                'name',
            ],
        ];

        $structuredAttributes = [
            'marketing' => [
                'sku',
            ],
        ];

        $attributeGroupRepository->find('marketing')->willReturn($marketing);

        $marketing->getId()->willReturn(1);

        $structuredAttributeRepository
            ->getStructuredAttributes(42, 'ecommerce', 'en_US')->willReturn($structuredAttributes);

        $familyRequirementRepository
            ->getStructuredAttributes($structuredAttributes['familyCode'], 'ecommerce', 'em_US')
            ->willReturn($mandatoryAttributes);

        $preProcessingRepository->addPreProcessingData(42, 1, 1, 0)->shouldBeCalled();

        $this->generate(42, 'ecommerce', 'en_US');
    }

    function it_generates_pre_processing_data_values(
        $preProcessingRepository,
        $familyRequirementRepository,
        $structuredAttributeRepository,
        $attributeGroupRepository,
        AttributeGroupInterface $marketing,
        AttributeGroupInterface $technical
    ) {
        $mandatoryAttributes = [
            'marketing' => [
                'sku',
                'name',
                'description',
            ],
            'technical' => [
                'weight',
                'height',
            ],
        ];

        $structuredAttributes = [
        ];

        $attributeGroupRepository->find('marketing')->willReturn($marketing);
        $attributeGroupRepository->find('technical')->willReturn($technical);

        $marketing->getId()->willReturn(1);
        $technical->getId()->willReturn(2);

        $structuredAttributeRepository
            ->getStructuredAttributes(42, 'ecommerce', 'en_US')->willReturn($structuredAttributes);

        $familyRequirementRepository
            ->getStructuredAttributes($structuredAttributes['familyCode'], 'ecommerce', 'em_US')
            ->willReturn($mandatoryAttributes);

        $preProcessingRepository->addPreProcessingData(42, 1, 0, 0)->shouldBeCalled();
        $preProcessingRepository->addPreProcessingData(42, 2, 0, 0)->shouldBeCalled();

        $this->generate(42, 'ecommerce', 'en_US');
    }
}
