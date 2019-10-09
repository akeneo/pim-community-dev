<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family\FindAttributesForFamily;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family\ComputeCompletenessOnFamilyUpdateSubscriber;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
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
        AttributeRequirementRepositoryInterface $attributeRequirementRepository,
        FindAttributesForFamily $findAttributesForFamily
    ) {
        $this->beConstructedWith(
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
            $attributeRequirementRepository,
            'my_job_name',
            $findAttributesForFamily
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOnFamilyUpdateSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE  => 'checkIfUpdateNeedsToRunBackgroundJob',
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsFamily',
        ]);
    }

    function it_detects_that_the_attribute_requirements_of_the_family_changed_on_pre_save_and_run_job_on_post_save(
        $attributeRequirementRepository,
        $tokenStorage,
        $jobInstanceRepository,
        $jobLauncher,
        $findAttributesForFamily,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement1,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        ArrayCollection $attributes
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

        $findAttributesForFamily->execute($family)->willReturn(['price', 'text']);
        $family->getAttributes()->willReturn($attributes);
        $attributes->map(Argument::type(\Closure::class))->willReturn($attributes);
        $attributes->toArray()->willReturn(['text', 'price']);

        $this->checkIfUpdateNeedsToRunBackgroundJob($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_detects_that_the_attribute_list_of_the_family_changed_on_pre_save_and_run_job_on_post_save(
        $attributeRequirementRepository,
        $tokenStorage,
        $jobInstanceRepository,
        $jobLauncher,
        $findAttributesForFamily,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement1,
        AttributeRequirementInterface $attributeRequirement2,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        ArrayCollection $attributes
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

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('my_job_name')->willReturn($jobInstance);
        $family->getCode()->willReturn('accessories');
        $jobLauncher->launch($jobInstance, $user, ['family_code' => 'accessories'])->shouldBeCalled();

        $findAttributesForFamily->execute($family)->willReturn(['price', 'text']);
        $family->getAttributes()->willReturn($attributes);
        $attributes->map(Argument::type(\Closure::class))->willReturn($attributes);
        $attributes->toArray()->willReturn(['text', 'price', 'description', 'name']);

        $this->checkIfUpdateNeedsToRunBackgroundJob($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_detects_it_does_not_need_to_run_the_job_on_pre_save_and_does_not_run_job_on_post_save(
        $attributeRequirementRepository,
        $jobLauncher,
        $findAttributesForFamily,
        GenericEvent $event,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement1,
        AttributeRequirementInterface $attributeRequirement2,
        ArrayCollection $attributes
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

        $findAttributesForFamily->execute($family)->willReturn(['price', 'text']);
        $family->getAttributes()->willReturn($attributes);
        $attributes->map(Argument::type(\Closure::class))->willReturn($attributes);
        $attributes->toArray()->willReturn(['text', 'price']);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->checkIfUpdateNeedsToRunBackgroundJob($event);
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

        $this->checkIfUpdateNeedsToRunBackgroundJob($event);
        $this->computeCompletenessOfProductsFamily($event);
    }

    function it_only_handles_family_objects(
        $jobLauncher,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn(new \StdClass());

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->checkIfUpdateNeedsToRunBackgroundJob($event);
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

        $this->checkIfUpdateNeedsToRunBackgroundJob($event);
        $this->computeCompletenessOfProductsFamily($event);
    }
}
