<?php

namespace spec\Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueProcessorSpec extends ObjectBehavior
{
    function let(ProductUpdaterInterface $productUpdater, ValidatorInterface $validator)
    {
        $this->beConstructedWith(
            $productUpdater,
            $validator
        );
    }

    function it_adds_values_to_product(
        $productUpdater,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        ConstraintViolationListInterface $violations
    ) {
        $actions = [
                        [
                            'field' => 'categories',
                            'value' => ['office', 'bedroom'],
                        ]
                  ];

        $item = ['product' => $product, 'actions' => $actions];

        $validator->validate($product)->willReturn($violations);
        $violations->count()->willReturn(0);

        $productUpdater->addData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->process($item);
    }

    function it_adds_invalid_values_to_product(
        $productUpdater,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        ConstraintViolationListInterface $violations
    ) {
        $actions = [
                        [
                            'field' => 'categories',
                            'value' => ['office', 'bedroom'],
                        ]
                  ];

        $item = ['product' => $product, 'actions' => $actions];

        $validator->validate($product)->willReturn($violations);

        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);

        $violations = new ConstraintViolationList([$violation, $violation]);

        $validator->validate($product)->willReturn($violations);

        $productUpdater->addData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->process($item);
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
