<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Updater\UpdaterInterface;
use Pim\Component\Connector\Processor\Denormalization\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;

class AttributeOptionProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $optionRepository,
        UpdaterInterface $optionUpdater,
        StepExecution $stepExecution
    ) {
        $optionClass = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
        $this->beConstructedWith(
            $arrayConverter,
            $optionRepository,
            $optionUpdater,
            $optionClass
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_updates_an_existing_attribute_option(
        $arrayConverter,
        $optionRepository,
        $optionUpdater,
        AttributeOptionInterface $option
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn($option);
        $option->getId()->willReturn(42);

        $arrayConverter
            ->convert(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->willReturn(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12]);
        $optionUpdater
            ->update($option, ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])->shouldBeCalled();

        $this
            ->process(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldReturn($option);
    }

    function it_creates_an_attribute_option(
        $arrayConverter,
        $optionRepository,
        $optionUpdater
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn(null);

        $arrayConverter
            ->convert(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->willReturn(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12]);
        $optionUpdater
            ->update(Argument::any(), ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])->shouldBeCalled();

        $this
            ->process(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeOption');
    }

    function it_skips_an_attribute_option_when_data_is_invalid(
        $arrayConverter,
        $optionRepository,
        $optionUpdater
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn(null);

        $arrayConverter
            ->convert(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->willReturn(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12]);
        $optionUpdater
            ->update(Argument::any(), ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->willThrow(new BusinessValidationException(new ConstraintViolationList()));

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12]]
            );
    }
}
