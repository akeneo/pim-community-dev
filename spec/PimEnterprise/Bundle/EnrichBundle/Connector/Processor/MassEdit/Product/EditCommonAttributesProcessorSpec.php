<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        ObjectUpdaterInterface $productUpdater,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $validator,
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
        JobExecution $jobExecution,
        UserInterface $owner
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
        $normalizedValues = json_encode($values);

        $configuration = [
            'filters' => [],
            'actions' => [
                'normalized_values' => $normalizedValues,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US'
            ]
        ];
        $this->setConfiguration($configuration);

        $this->setStepExecution($stepExecution);
        $jobExecution->getUser()->willReturn('owner');
        $userManager->findUserByUsername('owner')->willReturn($owner);
        $owner->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

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
        JobExecution $jobExecution,
        UserInterface $editor
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
        $normalizedValues = json_encode($values);

        $configuration = [
            'filters' => [],
            'actions' => [
                'normalized_values' => $normalizedValues,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US'
            ]
        ];
        $this->setConfiguration($configuration);

        $this->setStepExecution($stepExecution);
        $jobExecution->getUser()->willReturn('editor');
        $userManager->findUserByUsername('editor')->willReturn($editor);
        $editor->getRoles()->willReturn([]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);

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
        JobExecution $jobExecution,
        UserInterface $anon
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
        $normalizedValues = json_encode($values);
        $configuration = [
            'filters' => [],
            'actions' => [
                'normalized_values' => $normalizedValues,
                'current_locale'    => 'en_US'
            ]
        ];
        $this->setConfiguration($configuration);
        $this->setStepExecution($stepExecution);
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
