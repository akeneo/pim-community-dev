<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FamilyProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $familyConverter,
        FamilyFactory $familyFactory,
        ObjectUpdaterInterface $familyUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $familyConverter, $familyFactory, $familyUpdater, $validator);
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

    function it_updates_an_existing_family(
        $familyConverter,
        $repository,
        $familyUpdater,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($family);

        $family->getId()->willReturn(42);

        $values = $this->getValues();

        $familyConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $familyUpdater
            ->update($family, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($family)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($family);
    }

    function it_skips_a_family_when_update_fails(
        $familyConverter,
        $repository,
        $familyUpdater,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($family);

        $family->getId()->willReturn(42);

        $values = $this->getValues();

        $familyConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $familyUpdater
            ->update($family, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($family)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($family);

        $familyUpdater
            ->update($family, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_family_when_object_is_invalid(
        $familyConverter,
        $repository,
        $familyUpdater,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($family);

        $family->getId()->willReturn(42);

        $values = $this->getValues();

        $familyConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $familyUpdater
            ->update($family, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($family)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($family);

        $familyUpdater
            ->update($family, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($family)
            ->willReturn($violations);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function getValues()
    {
        return [
            'original_values' => [
                'code'                => 'mycode',
                'label-en_US'         => 'PC Monitors',
                'label-fr_FR'         => 'Moniteurs',
                'attributes'          => 'sku,name,description,price',
                'attribute_as_label'  => 'name',
                'requirements-print'  => 'sku,name,description',
                'requirements-mobile' => 'sku,name',
            ],
            'converted_values' => [
                'code'                => 'mycode',
                'attributes'          => ['sku', 'name', 'description', 'price'],
                'attribute_as_label'  => 'name',
                'requirements' => [
                    'mobile' => ['sku', 'name'],
                    'print'  => ['sku', 'name', 'description'],
                ],
                'labels' => [
                    'fr_FR' => 'Moniteurs',
                    'en_US' => 'PC Monitors',
                ],
            ]
        ];
    }
}
