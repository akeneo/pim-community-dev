<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher
    ) {
        $this->beConstructedWith(
            $validator,
            $productRepository,
            $jobConfigurationRepo,
            $productUpdater,
            $productDetacher
        );
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_does_not_set_values_when_attribute_is_not_editable(
        $validator,
        $productUpdater,
        $productDetacher,
        $productRepository,
        ProductInterface $product,
        ProductValueInterface $productValue,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $this->setStepExecution($stepExecution);

        $product->getIdentifier()->shouldBeCalled()->willReturn($productValue);
        $product->getId()->willReturn(10);
        $productValue->getData()->shouldBeCalled();

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning(
            'edit_common_attributes_processor',
            'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
            [],
            Argument::type('array')
        )->shouldBeCalled();

        $productDetacher->detach($product)->shouldBeCalled();

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $normalizedValues = addslashes(json_encode([
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ]));

        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues,
                        'ui_locale'         => 'en_US',
                        'attribute_locale'  => 'en_US',
                        'attribute_channel' => null
                    ]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $productRepository->hasAttributeInFamily(10, 'categories')->shouldBeCalled()->willReturn(true);
        $productRepository->hasAttributeInVariantGroup(10, 'categories')->shouldBeCalled()->willReturn(true);
        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_sets_values_to_attributes(
        $validator,
        $productUpdater,
        $productRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $values = [
            'number' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => '2.5'
                ]
            ]
        ];
        $normalizedValues = addslashes(json_encode($values));

        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues,
                        'ui_locale'         => 'fr_FR',
                        'attribute_locale'  => 'en_US',
                        'attribute_channel' => null
                    ]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $productRepository->hasAttributeInFamily(10, 'number')->shouldBeCalled()->willReturn(true);
        $productRepository->hasAttributeInVariantGroup(10, 'number')->shouldBeCalled()->willReturn(false);

        $productUpdater->update($product, $values)->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_invalid_values_to_attributes(
        $validator,
        $productUpdater,
        $productRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violations,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];
        $normalizedValues = addslashes(json_encode($values));

        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues,
                        'ui_locale'         => 'fr_FR',
                        'attribute_locale'  => 'en_US',
                        'attribute_channel' => null
                    ]
                ]
            )
        );

        $validator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $product->getId()->willReturn(10);
        $productRepository->hasAttributeInFamily(10, 'categories')->shouldBeCalled()->willReturn(true);
        $productRepository->hasAttributeInVariantGroup(10, 'categories')->shouldBeCalled()->willReturn(false);

        $productUpdater->update($product, $values)->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product);
    }
}
