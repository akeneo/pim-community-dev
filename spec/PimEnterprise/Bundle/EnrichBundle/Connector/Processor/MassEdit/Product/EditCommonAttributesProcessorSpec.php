<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $validator,
            $productRepository,
            $productUpdater,
            $productDetacher,
            $userManager,
            $tokenStorage,
            $authorizationChecker
        );
        $this->setStepExecution($stepExecution);
    }

    function it_sets_values_if_user_is_a_product_owner(
        $validator,
        $productUpdater,
        $userManager,
        $authorizationChecker,
        $productRepository,
        $stepExecution,
        AttributeInterface $attribute,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $owner,
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $jobExecution->getUser()->willReturn('owner');
        $userManager->findUserByUsername('owner')->willReturn($owner);
        $owner->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);
        $product->isAttributeEditable($attribute)->willReturn(true);
        $product->getId()->willReturn(42);
        $productRepository->hasAttributeInFamily(42, 'categories')->willReturn(true);

        $productUpdater->update($product, ['values' => $values])->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_values_if_user_is_a_product_editor(
        $validator,
        $productUpdater,
        $userManager,
        $authorizationChecker,
        $productRepository,
        $stepExecution,
        AttributeInterface $attribute,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $editor,
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

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $jobExecution->getUser()->willReturn('editor');
        $userManager->findUserByUsername('editor')->willReturn($editor);
        $editor->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $product->isAttributeEditable($attribute)->willReturn(true);
        $product->getId()->willReturn(42);
        $productRepository->hasAttributeInFamily(42, 'categories')->willReturn(true);
        $productUpdater->update($product, ['values' => $values])->shouldBeCalled();

        $this->process($product);
    }

    function it_does_not_set_values_if_user_is_not_allowed_to_edit_the_product(
        $productUpdater,
        $userManager,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product,
        JobExecution $jobExecution,
        UserInterface $anon,
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

        $jobExecution->getUser()->willReturn('anon');
        $userManager->findUserByUsername('anon')->willReturn($anon);
        $anon->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo("skipped_products")->shouldBeCalled();
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->process($product);
    }
}
