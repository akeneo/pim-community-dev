<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        ObjectUpdaterInterface $productUpdater,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $validator,
            $massActionRepository,
            $attributeRepository,
            $jobConfigurationRepo,
            $productUpdater,
            $userManager,
            $tokenStorage,
            $authorizationChecker
        );
    }

    function it_sets_values_if_user_is_a_product_owner(
        $validator,
        $productUpdater,
        $userManager,
        $authorizationChecker,
        AttributeInterface $attribute,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
        UserInterface $owner
    ) {
        $this->setStepExecution($stepExecution);
        $jobExecution->getUser()->willReturn('owner');
        $userManager->findUserByUsername('owner')->willReturn($owner);
        $owner->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];
        $normalizedValues = addslashes(json_encode($values));

        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues
                    ]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $product->isAttributeEditable($attribute)->willReturn(true);
        $productUpdater->update($product, $values)->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_values_if_user_is_a_product_editor(
        $validator,
        $productUpdater,
        $userManager,
        $authorizationChecker,
        AttributeInterface $attribute,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
        UserInterface $editor
    ) {
        $this->setStepExecution($stepExecution);
        $jobExecution->getUser()->willReturn('editor');
        $userManager->findUserByUsername('editor')->willReturn($editor);
        $editor->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];
        $normalizedValues = addslashes(json_encode($values));

        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues
                    ]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $product->isAttributeEditable($attribute)->willReturn(true);
        $productUpdater->update($product, $values)->shouldBeCalled();

        $this->process($product);
    }

    function it_does_not_set_values_if_user_is_not_allowed_to_edit_the_product(
        $productUpdater,
        $userManager,
        $authorizationChecker,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
        UserInterface $anon
    ) {
        $this->setStepExecution($stepExecution);
        $jobExecution->getUser()->willReturn('anon');
        $userManager->findUserByUsername('anon')->willReturn($anon);
        $anon->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo("skipped_products")->shouldBeCalled();
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];
        $normalizedValues = addslashes(json_encode($values));

        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [
                        'normalized_values' => $normalizedValues
                    ]
                ]
            )
        );

        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->process($product);
    }
}
