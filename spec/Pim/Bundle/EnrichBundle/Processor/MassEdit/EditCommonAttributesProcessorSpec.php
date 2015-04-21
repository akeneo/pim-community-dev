<?php

namespace spec\Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $productUpdater,
            $validator,
            $massActionRepository,
            $attributeRepository
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

    function it_sets_values_to_attributes(
        $validator,
        $productUpdater,
        FamilyInterface $family,
        AttributeInterface $attribute,
        MyCustomAttributeRepository $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violations
    ) {
        $actions = [
            [
                'field'   => 'categories',
                'value'   => ['office', 'bedroom'],
                'options' => [],
            ]
        ];

        $item = ['product' => $product, 'actions' => $actions];

        $validator->validate($product)->willReturn($violations);
        $violations->count()->willReturn(0);

        $attributeRepository->findOneByCode('categories')->willReturn($attribute);
        $family->hasAttribute($attribute)->willReturn(true);
        $product->getFamily()->willReturn($family);

        $productUpdater->setData($product, 'categories', ['office', 'bedroom'], [])->shouldBeCalled();

        $this->process($item);
    }

    function it_sets_invalid_values_to_attributes(
        $validator,
        $productUpdater,
        FamilyInterface $family,
        AttributeInterface $attribute,
        MyCustomAttributeRepository $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violations,
        StepExecution $stepExecution
    ) {
        $actions = [
            [
                'field'   => 'categories',
                'value'   => ['office', 'bedroom'],
                'options' => [],
            ]
        ];

        $item = ['product' => $product, 'actions' => $actions];

        $validator->validate($product)->willReturn($violations);

        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);

        $violations = new ConstraintViolationList([$violation, $violation]);

        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneByCode('categories')->willReturn($attribute);
        $family->hasAttribute($attribute)->willReturn(true);
        $product->getFamily()->willReturn($family);


        $productUpdater->setData($product, 'categories', ['office', 'bedroom'], [])->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($item);
    }
}

class MyCustomAttributeRepository
{
    public function findOneByCode() {}
}
