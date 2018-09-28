<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamilyVariant\CheckAttributeEditable;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditAttributesProcessorSpec extends ObjectBehavior
{
    // @todo merge : remove $userManager and $tokenStorage in master branch. They are no longer used.
    function let(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productValidator,
            $productModelValidator,
            $productUpdater,
            $productModelUpdater,
            $attributeRepository,
            $checkAttributeEditable,
            $userManager,
            $tokenStorage,
            $authorizationChecker
        );
        $this->setStepExecution($stepExecution);
    }

    function it_sets_values_if_user_is_a_product_owner(
        $productValidator,
        $productUpdater,
        $authorizationChecker,
        $stepExecution,
        $attributeRepository,
        $checkAttributeEditable,
        AttributeInterface $attribute,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];

        $configuration = [
            'filters' => [],
            'actions' => [[
                'normalized_values' => $values,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US'
            ]]
        ];

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $checkAttributeEditable->isEditable($product, $attribute)->willReturn(true);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(42);

        $productUpdater->update($product, ['values' => $values])->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_values_if_user_is_a_product_editor(
        $productValidator,
        $productUpdater,
        $authorizationChecker,
        $stepExecution,
        $attributeRepository,
        $checkAttributeEditable,
        AttributeInterface $attribute,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];

        $configuration = [
            'filters' => [],
            'actions' => [[
                'normalized_values' => $values,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US'
            ]]
        ];

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $checkAttributeEditable->isEditable($product, $attribute)->willReturn(true);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $product->isAttributeEditable($attribute)->willReturn(true);
        $product->getId()->willReturn(42);
        $productUpdater->update($product, ['values' => $values])->shouldBeCalled();

        $this->process($product);
    }

    function it_does_not_set_values_if_user_is_not_allowed_to_edit_the_product(
        $productUpdater,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $values = [
            'categories' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => ['office', 'bedroom']
                ]
            ]
        ];
        $configuration = [
            'filters' => [],
            'actions' => [[
                'normalized_values' => $values,
                'current_locale'    => 'en_US'
            ]]
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $stepExecution->incrementSummaryInfo("skipped_products")->shouldBeCalled();
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->process($product);
    }
}
