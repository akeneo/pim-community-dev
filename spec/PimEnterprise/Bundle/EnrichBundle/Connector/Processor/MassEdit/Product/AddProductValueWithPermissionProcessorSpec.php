<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddProductValueWithPermissionProcessorSpec extends ObjectBehavior
{
    // @todo merge : remove $userManager and $tokenStorage in master branch. They are no longer used.
    function let(
        PropertyAdderInterface $productFieldUpdater,
        ValidatorInterface $validator,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productFieldUpdater,
            $validator,
            $userManager,
            $authorizationChecker,
            $tokenStorage
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\StepExecutionAwareInterface');
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_processes(
        $authorizationChecker,
        $validator,
        $stepExecution,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $configuration = ['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]];
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $this->process($product)->shouldReturn($product);
    }

    function it_processes_without_permissions(
        $authorizationChecker,
        $stepExecution,
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
        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
            [],
            Argument::type('Akeneo\Component\Batch\Item\InvalidItemInterface')
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $this->process($product)->shouldReturn(null);
    }
}
