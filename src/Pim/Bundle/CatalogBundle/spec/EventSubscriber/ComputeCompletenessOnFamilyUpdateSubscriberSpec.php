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

        $family->getCode()->willReturn('accessories');
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_does_not_run_the_job_for_new_families(
        $jobLauncher,
        GenericEvent $event,
        FamilyInterface $family
    ) {
        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $family->getId()->willReturn(null);
        $family->getCode()->willReturn('accessories');
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->areAttributeRequirementsUpdated($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_handles_conccurent_pre_and_post_save_for_different_families(
        $attributeRequirementRepository,
        $tokenStorage,
        $jobInstanceRepository,
        $jobLauncher,
        GenericEvent $event1,
        GenericEvent $event2,
        FamilyInterface $family1,
        FamilyInterface $family2,
        AttributeRequirementInterface $attributeRequirement1,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $event1->getSubject()->willReturn($family1);
        $event1->hasArgument('unitary')->willReturn(true);
        $event1->getArgument('unitary')->willReturn(true);
        $family1->getId()->willReturn(152);
        $family1->getCode()->willReturn('accessories');
        $attributeRequirementRepository->findRequiredAttributesCodesByFamily($family1)->willReturn(
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
        $family1->getAttributeRequirements()->willReturn(
            [
                'price_ecommerce' => $attributeRequirement1
            ]
        );

        $event2->getSubject()->willReturn($family2);
        $event2->hasArgument('unitary')->willReturn(true);
        $event2->getArgument('unitary')->willReturn(true);
        $family2->getId()->willReturn(93);
        $family2->getCode()->willReturn('shorts');
        $attributeRequirementRepository->findRequiredAttributesCodesByFamily($family2)->willReturn(
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
        $family2->getAttributeRequirements()->willReturn(
            [
                'price_ecommerce' => $attributeRequirement1
            ]
        );

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('my_job_name')->willReturn($jobInstance);
        $jobLauncher->launch($jobInstance, $user, ['family_code' => 'accessories'])->shouldBeCalled();
        $jobLauncher->launch($jobInstance, $user, ['family_code' => 'shorts'])->shouldBeCalled();

        $this->areAttributeRequirementsUpdated($event1);
        $this->areAttributeRequirementsUpdated($event2);
        $this->computeCompletenessOfProductsFamily($event1);
        $this->computeCompletenessOfProductsFamily($event2);
    }
}
