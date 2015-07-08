<?php

namespace spec\PimEnterprise\Component\ProductAsset\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Factory\TagFactory;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class TagProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $tagConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $tagUpdater,
        TagFactory $tagFactory,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($tagConverter, $repository, $tagUpdater, $tagFactory, $validator);
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

    function it_updates_an_existing_tag(
        $tagConverter,
        $repository,
        $tagUpdater,
        $validator,
        TagInterface $tag1,
        TagInterface $tag2,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('dog')->willReturn($tag1);
        $repository->findOneByIdentifier('flowers')->willReturn($tag2);

        $tag1->getId()->willReturn(42);
        $tag2->getId()->willReturn(22);

        $values = $this->getValues();

        $tagConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $tagUpdater
            ->update($tag1, ['code' => 'dog'])
            ->shouldBeCalled();

        $tagUpdater
            ->update($tag2, ['code' => 'flowers'])
            ->shouldBeCalled();

        $validator
            ->validate($tag1)
            ->willReturn($violationList);

        $validator
            ->validate($tag2)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn([$tag1, $tag2]);
    }

    function it_skips_a_tag_when_update_fails(
        $tagConverter,
        $repository,
        $tagUpdater,
        $validator,
        TagInterface $tag1,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('dog')->willReturn($tag1);

        $tag1->getId()->willReturn(42);

        $values = $this->getValues();

        $tagConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $tagUpdater
            ->update($tag1, ['code' => 'dog'])
            ->willThrow(new \InvalidArgumentException());

        $validator
            ->validate($tag1)
            ->willReturn($violationList);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_tag_when_object_is_invalid(
        $tagConverter,
        $repository,
        $tagUpdater,
        $validator,
        TagInterface $tag1,
        TagInterface $tag2,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('dog')->willReturn($tag1);
        $repository->findOneByIdentifier('flowers')->willReturn($tag2);

        $tag1->getId()->willReturn(42);
        $tag2->getId()->willReturn(22);

        $values = $this->getValues();

        $tagConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $tagUpdater
            ->update($tag1, ['code' => 'dog'])
            ->shouldBeCalled();

        $tagUpdater
            ->update($tag2, ['code' => 'flowers'])
            ->shouldBeCalled();

        $validator
            ->validate($tag1)
            ->willReturn($violationList);

        $validator
            ->validate($tag2)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn([$tag1, $tag2]);

        $tagUpdater
            ->update($tag1, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $tagUpdater
            ->update($tag2, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($tag1)
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
            'original_values'  => [
                'code'          => 'mycode',
                'localized'     => 0,
                'description'   => 'My awesome description',
                'qualification' => 'dog,flowers',
                'end_of_use_at' => '2018/02/01',
            ],
            'converted_values' => [
                'code'          => 'mycode',
                'localized'     => false,
                'description'   => 'My awesome description',
                'tags'          => ['dog', 'flowers'],
                'end_of_use_at' => '2018/02/01',
            ],
        ];
    }
}
