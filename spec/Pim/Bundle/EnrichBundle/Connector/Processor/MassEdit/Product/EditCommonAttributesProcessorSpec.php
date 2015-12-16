<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        LocalizerRegistryInterface $localizerRegistry,
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $validator,
            $massActionRepository,
            $attributeRepository,
            $jobConfigurationRepo,
            $localizerRegistry,
            $productUpdater
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
        AttributeInterface $attribute,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo("skipped_products")->shouldBeCalled();
        $stepExecution->addWarning(
            'edit_common_attributes_processor',
            'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
            [],
            $product
        )->shouldBeCalled();

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
                        'current_locale'    => 'en_US'
                    ]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $product->isAttributeEditable($attribute)->willReturn(false);
        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_sets_values_to_attributes(
        $validator,
        $propertySetter,
        $localizerRegistry,
        $productUpdater,
        AttributeInterface $attribute,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
        LocalizerInterface $localizer
    ) {
        $this->setStepExecution($stepExecution);
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
                    'filters'           => [],
                    'actions'           => [
                        [
                            'field' => 'categories',
                            'value' => [
                                '2,5'
                            ],
                            'options' => []
                        ]
                    ],
                    'locale' => 'fr_FR'
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues,
                        'current_locale'    => 'en_US'
                    ]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $attribute->getAttributeType()->willReturn('multi_select');
        $attributeRepository->findOneBy(['code' => 'categories'])->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $product->isAttributeEditable($attribute)->willReturn(true);

        $localizerRegistry->getLocalizer('multi_select')->willReturn($localizer);
        $localizer->delocalize(['2,5'], ['locale' => 'fr_FR'])->willReturn('2.5');

        $propertySetter->setData($product, 'categories', '2.5', [])->shouldBeCalled();
        $productUpdater->update($product, $values)->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_invalid_values_to_attributes(
        $validator,
        $productUpdater,
        AttributeInterface $attribute,
        AttributeRepositoryInterface $attributeRepository,
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
                        'current_locale'    => 'en_US'
                    ]
                ]
            )
        );

        $validator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $product->isAttributeEditable($attribute)->willReturn(true);

        $productUpdater->update($product, $values)->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product);
    }
}
