<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        GroupFactory $groupFactory,
        ObjectUpdaterInterface $variantUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $groupFactory, $variantUpdater, $validator, $detacher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_variant_group(
        $repository,
        $variantUpdater,
        $validator,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($variantGroup);
        $groupType = new GroupType();
        $groupType->setVariant(true);

        $productTemplate->getValues()->willReturn(new ArrayCollection());

        $variantGroup->getType()->willReturn($groupType);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);

        $values = $this->getValues();

        $variantUpdater
            ->update($variantGroup, $values)
            ->shouldBeCalled();

        $validator
            ->validate($variantGroup)
            ->willReturn($violationList);

        $this
            ->process($values)
            ->shouldReturn($variantGroup);
    }

    function it_skips_a_variant_group_when_update_fails(
        $repository,
        $variantUpdater,
        $validator,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($variantGroup);
        $groupType = new GroupType();
        $groupType->setVariant(true);

        $productTemplate->getValues()->willReturn(new ArrayCollection());

        $variantGroup->getType()->willReturn($groupType);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);

        $values = $this->getValues();

        $variantUpdater
            ->update($variantGroup, $values)
            ->shouldBeCalled();

        $validator
            ->validate($variantGroup)
            ->willReturn($violationList);

        $this
            ->process($values)
            ->shouldReturn($variantGroup);

        $variantUpdater
            ->update($variantGroup, $values)
            ->willThrow(new \InvalidArgumentException('Attributes: This property cannot be changed.'));

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values]
            );
    }

    function it_skips_a_variant_group_when_object_is_invalid(
        $repository,
        $variantUpdater,
        $validator,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($variantGroup);
        $groupType = new GroupType();
        $groupType->setVariant(true);

        $productTemplate->getValues()->willReturn(new ArrayCollection());

        $variantGroup->getType()->willReturn($groupType);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);

        $values = $this->getValues();

        $variantUpdater
            ->update($variantGroup, $values)
            ->shouldBeCalled();

        $validator
            ->validate($variantGroup)
            ->willReturn($violationList);

        $this
            ->process($values)
            ->shouldReturn($variantGroup);

        $variantUpdater
            ->update($variantGroup, $values)
            ->willThrow(new \InvalidArgumentException('Attributes: This property cannot be changed.'));

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($variantGroup)
            ->willReturn($violations);

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values]
            );
    }

    function getValues()
    {
        return [
            'code'         => 'mycode',
            'axis'         => ['main_color', 'secondary_color'],
            'type'         => 'VARIANT',
            'labels'       => [
                'fr_FR' => 'T-shirt super beau',
                'en_US' => 'T-shirt very beautiful',
            ],
            'values' => [
                'main_color'   => 'white',
                'tshirt_style' => ['turtleneck', 'sportwear'],
                'description'  => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                ]
            ]
        ];
    }
}
