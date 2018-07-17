<?php

namespace spec\Akeneo\Pim\Permission\Bundle\MassEdit\Processor;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyRemoverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveProductValueWithPermissionProcessorSpec extends ObjectBehavior
{
    function let(
        PropertyRemoverInterface $propertyRemover,
        ValidatorInterface $validator,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $propertyRemover,
            $validator,
            $userManager,
            $authorizationChecker,
            $tokenStorage
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Tool\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('Akeneo\Tool\Bundle\BatchBundle\Item\StepExecutionAwareInterface');
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_should_processes(
        $authorizationChecker,
        $tokenStorage,
        $userManager,
        $validator,
        $stepExecution,
        JobExecution $jobExecution,
        UserInterface $userJulia,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $configuration = ['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]];
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $userManager->findUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $this->process($product)->shouldReturn($product);
    }

    function it_should_processes_without_permissions(
        $authorizationChecker,
        $tokenStorage,
        $userManager,
        $stepExecution,
        JobExecution $jobExecution,
        UserInterface $userJulia,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $configuration  = [
            'filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
            [],
            Argument::type('Akeneo\Tool\Component\Batch\Item\InvalidItemInterface')
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $userManager->findUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $this->process($product)->shouldReturn(null);
    }
}
