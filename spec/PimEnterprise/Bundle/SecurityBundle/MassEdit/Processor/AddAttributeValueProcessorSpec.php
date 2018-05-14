<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\MassEdit\Processor;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Manager\UserManager;
use Pim\Component\Catalog\EntityWithFamilyVariant\CheckAttributeEditable;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\User\Model\UserInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddAttributeValueProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        PropertyAdderInterface $propertyAdder,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $productValidator,
            $productModelValidator,
            $propertyAdder,
            $attributeRepository,
            $checkAttributeEditable,
            ['pim_catalog_multiselect', 'pim_reference_data_multiselect'],
            $userManager,
            $tokenStorage,
            $authorizationChecker
        );
    }

    function it_adds_values_if_user_is_a_product_owner(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        $userManager,
        $authorizationChecker,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute,
        AttributeInterface $suppliersAttribute,
        UserInterface $owner
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ],
                'suppliers' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'barret'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(true);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeRepository->findOneByIdentifier('suppliers')->willReturn($suppliersAttribute);
        $checkAttributeEditable->isEditable($product, $suppliersAttribute)->willReturn(true);
        $suppliersAttribute->getType()->willReturn('pim_catalog_multiselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $propertyAdder->addData(
            $product,
            'suppliers',
            'barret',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $jobExecution->getUser()->willReturn('owner');
        $userManager->findUserByUsername('owner')->willReturn($owner);
        $owner->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $this->process($product);
    }

    function it_adds_values_if_user_is_a_product_editor(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        $userManager,
        $authorizationChecker,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute,
        AttributeInterface $suppliersAttribute,
        UserInterface $owner
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ],
                'suppliers' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'barret'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(true);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeRepository->findOneByIdentifier('suppliers')->willReturn($suppliersAttribute);
        $checkAttributeEditable->isEditable($product, $suppliersAttribute)->willReturn(true);
        $suppliersAttribute->getType()->willReturn('pim_catalog_multiselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $propertyAdder->addData(
            $product,
            'suppliers',
            'barret',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $jobExecution->getUser()->willReturn('owner');
        $userManager->findUserByUsername('owner')->willReturn($owner);
        $owner->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $this->process($product);
    }

    function it_does_not_add_values_if_user_is_not_allowed_to_edit_the_product(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        $userManager,
        $authorizationChecker,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute,
        AttributeInterface $suppliersAttribute,
        UserInterface $owner
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ],
                'suppliers' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'barret'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(true);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeRepository->findOneByIdentifier('suppliers')->willReturn($suppliersAttribute);
        $checkAttributeEditable->isEditable($product, $suppliersAttribute)->willReturn(true);
        $suppliersAttribute->getType()->willReturn('pim_catalog_multiselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldNotBeCalled();

        $propertyAdder->addData(
            $product,
            'suppliers',
            'barret',
            ['scope' => null,'locale' => null]
        )->shouldNotBeCalled();

        $jobExecution->getUser()->willReturn('owner');
        $userManager->findUserByUsername('owner')->willReturn($owner);
        $owner->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product);
    }
}
