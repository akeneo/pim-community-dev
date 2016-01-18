<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Akeneo\Component\Classification\Factory\CategoryFactory;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $categoryConverter,
        IdentifiableObjectRepositoryInterface $repository,
        CategoryFactory $categoryFactory,
        ObjectUpdaterInterface $categoryUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($categoryConverter, $repository, $categoryUpdater, $categoryFactory, $validator);
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

    function it_updates_an_existing_category(
        $categoryConverter,
        $repository,
        $categoryUpdater,
        $validator,
        CategoryInterface $category,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($category);

        $category->getId()->willReturn(42);

        $values = $this->getValues();

        $categoryConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $categoryUpdater
            ->update($category, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($category)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($category);
    }

    function it_skips_a_category_when_update_fails(
        $categoryConverter,
        $repository,
        $categoryUpdater,
        $validator,
        CategoryInterface $category,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($category);

        $category->getId()->willReturn(42);

        $values = $this->getValues();

        $categoryConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $categoryUpdater
            ->update($category, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($category)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($category);

        $categoryUpdater
            ->update($category, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_category_when_object_is_invalid(
        $categoryConverter,
        $repository,
        $categoryUpdater,
        $validator,
        CategoryInterface $category,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($category);

        $category->getId()->willReturn(42);

        $values = $this->getValues();

        $categoryConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $categoryUpdater
            ->update($category, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($category)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($category);

        $categoryUpdater
            ->update($category, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($category)
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
                'parent'      => 'master',
                'label-fr_FR' => 'T-shirt super beau',
                'label-en_US' => 'T-shirt very beautiful',
            ],
            'converted_values' => [
                'code'         => 'mycode',
                'parent'       => 'master',
                'labels'       => [
                    'fr_FR' => 'T-shirt super beau',
                    'en_US' => 'T-shirt very beautiful',
                ],
            ]
        ];
    }
}
