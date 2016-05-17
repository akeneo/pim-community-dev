<?php

namespace spec\PimEnterprise\Component\Security\Connector\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Security\Model\LocaleAccessInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LocaleAccessProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $accessConverter,
        SimpleFactoryInterface $accessFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $accessConverter, $accessFactory, $updater, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_is_a_step_execution_aware_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_existing_locale_accesses_and_create_others(
        $accessConverter,
        $repository,
        $accessFactory,
        $updater,
        $validator,
        LocaleAccessInterface $localeAccessSupport,
        LocaleAccessInterface $localeAccessManager,
        ConstraintViolationListInterface $violationListSupport,
        ConstraintViolationListInterface $violationListManager
    ) {
        $repository->getIdentifierProperties()->willReturn(['locale', 'user_group']);
        $repository->findOneByIdentifier('en_US.Manager')->willReturn(null);
        $repository->findOneByIdentifier('en_US.IT support')->willReturn($localeAccessSupport);

        $localeAccessSupport->getId()->willReturn(42);
        $accessFactory->create()->willReturn($localeAccessManager);

        $values = $this->getValues();

        $accessConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $updater
            ->update($localeAccessSupport, $values['converted_values'][0])
            ->shouldBeCalled();
        $updater
            ->update($localeAccessManager, $values['converted_values'][1])
            ->shouldBeCalled();

        $validator
            ->validate($localeAccessManager)
            ->willReturn($violationListManager);

        $validator
            ->validate($localeAccessSupport)
            ->willReturn($violationListSupport);

        $this
            ->process($values['original_values'])
            ->shouldReturn([$localeAccessSupport, $localeAccessManager]);
    }

    protected function getValues()
    {
        return [
            'original_values' => [
                'locale'        => 'en_US',
                'view_products' => 'IT support,Manager',
                'edit_products' => 'IT support',
            ],
            'converted_values' => [
                [
                    'locale'        => 'en_US',
                    'user_group'     => 'IT support',
                    'view_products' => true,
                    'edit_products' => true,
                ], [
                    'locale'        => 'en_US',
                    'user_group'     => 'Manager',
                    'view_products' => true,
                    'edit_products' => false,
                ]
            ]
        ];
    }
}
