<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\CurrencyFactory;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CurrencyProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        CurrencyFactory $currencyFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $currencyFactory, $updater, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_currency(
        $repository,
        $updater,
        $validator,
        CurrencyInterface $currency,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('USD')->willReturn($currency);

        $currency->getId()->willReturn(42);

        $values = $this->getValues();

        $updater->update($currency, $values)->shouldBeCalled();

        $validator->validate($currency)->willReturn($violationList);

        $this->process($values)->shouldReturn($currency);
    }

    function it_skips_a_currency_when_update_fails(
        $repository,
        $updater,
        $validator,
        CurrencyInterface $currency,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('USD')->willReturn($currency);

        $currency->getId()->willReturn(42);

        $values = $this->getValues();

        $validator->validate($currency)->willReturn($violationList);

        $updater->update($currency, $values)->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during('process', [$values]);
    }

    function it_skips_a_currency_when_object_is_invalid(
        $repository,
        $validator,
        CurrencyInterface $currency
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('USD')->willReturn($currency);

        $currency->getId()->willReturn(42);

        $values = $this->getValues();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'sizes');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($currency)->willReturn($violations);

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
            'code'      => 'USD',
            'activated' => true,
        ];
    }
}
