<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\ComputeCompletenessOnFamilyUpdateSubscriber;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ComputeCompletenessOnFamilyUpdateSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        AttributeRequirementRepositoryInterface $attributeRequirementRepository
    ) {
      $this->beConstructedWith($tokenStorage, $jobLauncher, $jobInstanceRepository, $attributeRequirementRepository, 'my_job_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOnFamilyUpdateSubscriber::class);
    }

    function it_subsribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE  => 'areAttributeRequirementsUpdated',
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsFamily',
        ]);
    }

    function it_detects_that_the_attribute_requirements_of_the_family_changed_on_pre_save_and_run_job_on_post_save(
        $attributeRequirementRepository,
        $tokenStorage,
        $jobInstanceRepository,
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement1,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $family->getId()->willReturn(152);

        $attributeRequirementRepository->findRequiredAttributesCodesByFamily($family)->willReturn(
            [
                [
                    'attribute' => 'price',
                    'channel'   => 'ecommerce',
                ],
                [
                    'attribute' => 'text',
                    'channel'   => 'ecommerce',
                ],
            ]
        );

        $family->getAttributeRequirements()->willReturn(
            [
                'price_ecommerce' => $attributeRequirement1
            ]
        );

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('my_job_name')->willReturn($jobInstance);
        $family->getCode()->willReturn('accessories');
        $jobLauncher->launch($jobInstance, $user, ['family_code' => 'accessories'])->shouldBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_detects_that_the_attribute_requirements_of_the_family_has_not_changed_on_pre_save_and_do_not_run_job_on_post_save(
        $attributeRequirementRepository,
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement1,
        AttributeRequirementInterface $attributeRequirement2
    ) {
        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $family->getId()->willReturn(152);

        $attributeRequirementRepository->findRequiredAttributesCodesByFamily($family)->willReturn(
            [
                [
                    'attribute' => 'price',
                    'channel'   => 'ecommerce',
                ],
                [
                    'attribute' => 'text',
                    'channel'   => 'ecommerce',
                ],
            ]
        );

        $family->getAttributeRequirements()->willReturn(
            [
                'price_ecommerce' => $attributeRequirement1,
                'text_ecommerce' => $attributeRequirement2
            ]
        );

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_does_not_run_the_job_for_new_families(
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family
    ) {
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $family->getId()->willReturn(null);
        $event->getSubject()->willReturn($family);
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_only_handles_family_objects(
        $jobLauncher,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn(new \StdClass());

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_only_handles_unitary_events(
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family
    ) {
        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }
}
