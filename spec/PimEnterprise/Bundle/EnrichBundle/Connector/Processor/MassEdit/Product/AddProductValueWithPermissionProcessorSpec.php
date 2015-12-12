<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddProductValueWithPermissionProcessorSpec extends ObjectBehavior
{
    function let(
        PropertyAdderInterface $productFieldUpdater,
        ValidatorInterface $validator,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $productFieldUpdater,
            $validator,
            $jobConfigurationRepo,
            $userManager,
            $authorizationChecker,
            $tokenStorage
        );
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
        $tokenStorage,
        $userManager,
        $validator,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        UserInterface $userJulia,
        ProductInterface $product,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobConfigurationInterface $jobConfiguration
    ) {
        $this->setStepExecution($stepExecution);
        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $userManager->findUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]])
        );

        $this->process($product)->shouldReturn($product);
    }

    function it_processes_without_permissions(
        $authorizationChecker,
        $tokenStorage,
        $userManager,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        UserInterface $userJulia,
        ProductInterface $product,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobConfigurationInterface $jobConfiguration
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $stepExecution->addWarning(
            'add_product_value_with_permission_processor',
            'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
            [],
            $product
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $userManager->findUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]])
        );

        $this->process($product)->shouldReturn(null);
    }
}
