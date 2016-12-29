<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\PreProcessCompletenessStep;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

class PreProcessCompletenessStepSpec extends ObjectBehavior
{
    function let(
        PreProcessingRepositoryInterface $preProcessingRepository,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        ProductValueCompleteCheckerInterface $productValueChecker,
        FamilyInterface $family,
        ProductInterface $product,
        ProjectInterface $project,
        ChannelInterface $projectChannel,
        LocaleInterface $projectLocale,
        ProductValueInterface $skuValue,
        ProductValueInterface $nameValue,
        ProductValueInterface $weightValue,
        ProductValueInterface $heightValue,
        ProductValueInterface $documentationValue,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $weightAttribute,
        AttributeInterface $heightAttribute,
        AttributeInterface $documentationAttribute,
        AttributeGroupInterface $general,
        AttributeGroupInterface $marketing,
        AttributeGroupInterface $media
    ) {
        $this->beConstructedWith(
            $preProcessingRepository,
            $familyRequirementRepository,
            $productValueChecker
        );

        $projectChannel->getCode()->willReturn('ecommerce');
        $project->getChannel()->willReturn($projectChannel);

        $projectLocale->getCode()->willReturn('en_US');
        $project->getLocale()->willReturn($projectLocale);

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('camcorder');

        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $weightAttribute->getCode()->willReturn('weight');
        $heightAttribute->getCode()->willReturn('height');
        $documentationAttribute->getCode()->willReturn('documentation');

        $general->getId()->willReturn(40);
        $marketing->getId()->willReturn(33);
        $media->getId()->willReturn(64);

        $skuAttribute->getGroup()->willReturn($general);
        $nameAttribute->getGroup()->willReturn($general);
        $weightAttribute->getGroup()->willReturn($marketing);
        $heightAttribute->getGroup()->willReturn($marketing);
        $documentationAttribute->getGroup()->willReturn($media);

        $skuValue->getAttribute()->willReturn($skuAttribute);
        $nameValue->getAttribute()->willReturn($nameAttribute);
        $weightValue->getAttribute()->willReturn($weightAttribute);
        $heightValue->getAttribute()->willReturn($heightAttribute);
        $documentationValue->getAttribute()->willReturn($documentationAttribute);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessCompletenessStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_pre_processes_completeness_when_all_product_values_are_filled_for_an_attribute_group(
        $preProcessingRepository,
        $familyRequirementRepository,
        $productValueChecker,
        $product,
        $project,
        $projectChannel,
        $projectLocale,
        $skuValue,
        $nameValue,
        $weightValue,
        $heightValue,
        $documentationValue
    ) {
        $product->getValues()->willReturn([
            $skuValue,
            $nameValue,
            $weightValue,
            $heightValue,
            $documentationValue,
        ]);

        $productValueChecker->isComplete($skuValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($nameValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($weightValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($heightValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($documentationValue, $projectChannel, $projectLocale)->willReturn(true);


        $familyRequirementRepository->findRequiredAttributes($product, $project)->willReturn([
                40 => [
                    'sku',
                    'name',
                ],
                33 => [
                    'weight',
                    'height',
                ],
            ]);

        $preProcessingRepository->addAttributeGroup($product, $project, [
            [40, 0, 1],
            [33, 0, 1],
        ])->shouldBeCalled();

        $this->execute($product, $project)->shouldReturn(null);
    }

    function it_pre_processes_completeness_when_an_attribute_group_has_at_least_a_product_value_filled(
        $preProcessingRepository,
        $familyRequirementRepository,
        $productValueChecker,
        $product,
        $project,
        $projectChannel,
        $projectLocale,
        $skuValue,
        $documentationValue
    ) {
        $product->getValues()->willReturn([
            $skuValue,
            $documentationValue,
        ]);

        $productValueChecker->isComplete($skuValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($documentationValue, $projectChannel, $projectLocale)->willReturn(true);

        $familyRequirementRepository->findRequiredAttributes($product, $project)
            ->willReturn([
                40 => [
                    'sku',
                    'name',
                ],
            ]);

        $preProcessingRepository->addAttributeGroup($product, $project, [
            [40, 1, 0],
        ])->shouldBeCalled();

        $this->execute($product, $project);
    }

    function it_pre_processes_completeness_when_all_product_values_are_not_filled_for_an_attribute_group(
        $preProcessingRepository,
        $familyRequirementRepository,
        $productValueChecker,
        $product,
        $project,
        $projectChannel,
        $projectLocale,
        $documentationValue
    ) {
        $product->getValues()->willReturn([
            $documentationValue,
        ]);

        $productValueChecker->isComplete($documentationValue, $projectChannel, $projectLocale)->willReturn(true);

        $familyRequirementRepository->findRequiredAttributes($product, $project)
            ->willReturn([
                40 => [
                    'name',
                    'description',
                ],
                33 => [
                    'weight',
                    'height',
                ],
            ]);

        $preProcessingRepository->addAttributeGroup($product, $project, [
            [40, 0, 0],
            [33, 0, 0],
        ])->shouldBeCalled();

        $this->execute($product, $project);
    }
}
