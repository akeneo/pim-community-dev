<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AssociationTypeFactory;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssociationTypeProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $associationTypeConverter,
        IdentifiableObjectRepositoryInterface $repository,
        AssociationTypeFactory $associationTypeFactory,
        ObjectUpdaterInterface $associationTypeUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($associationTypeConverter, $repository, $associationTypeFactory, $associationTypeUpdater, $validator);
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

    function it_updates_an_existing_association_type(
        $associationTypeConverter,
        $repository,
        $associationTypeUpdater,
        $validator,
        AssociationTypeInterface $associationType,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($associationType);

        $associationType->getId()->willReturn(42);

        $values = $this->getValues();

        $associationTypeConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $associationTypeUpdater
            ->update($associationType, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($associationType)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($associationType);
    }

    function it_skips_a_association_type_when_update_fails(
        $associationTypeConverter,
        $repository,
        $associationTypeUpdater,
        $validator,
        AssociationTypeInterface $associationType,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($associationType);

        $associationType->getId()->willReturn(42);

        $values = $this->getValues();

        $associationTypeConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $associationTypeUpdater
            ->update($associationType, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($associationType)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($associationType);

        $associationTypeUpdater
            ->update($associationType, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_association_type_when_object_is_invalid(
        $associationTypeConverter,
        $repository,
        $associationTypeUpdater,
        $validator,
        AssociationTypeInterface $associationType,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($associationType);

        $associationType->getId()->willReturn(42);

        $values = $this->getValues();

        $associationTypeConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $associationTypeUpdater
            ->update($associationType, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($associationType)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($associationType);

        $associationTypeUpdater
            ->update($associationType, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($associationType)
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
                'code'        => 'mycode',
                'label-fr_FR' => 'T-shirt super beau',
                'label-en_US' => 'T-shirt very beautiful',
            ],
            'converted_values' => [
                'code'         => 'mycode',
                'labels'       => [
                    'fr_FR' => 'T-shirt super beau',
                    'en_US' => 'T-shirt very beautiful',
                ],
            ]
        ];
    }
}
