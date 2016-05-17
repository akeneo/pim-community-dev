<?php

namespace spec\PimEnterprise\Component\Security\Connector\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssetCategoryAccessProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $accessConverter,
        SimpleFactoryInterface $accessFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $accessConverter, $accessFactory, $updater, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_is_a_step_execution_aware_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_existing_locale_accesses_and_create_others(
        $accessConverter,
        $repository,
        $accessFactory,
        $updater,
        $validator,
        AssetCategoryAccess $accessSupport,
        AssetCategoryAccess $accessManager,
        ConstraintViolationListInterface $violationListSupport,
        ConstraintViolationListInterface $violationListManager
    ) {
        $repository->getIdentifierProperties()->willReturn(['category', 'user_group']);
        $repository->findOneByIdentifier('videos.Manager')->willReturn(null);
        $repository->findOneByIdentifier('videos.IT support')->willReturn($accessSupport);

        $accessSupport->getId()->willReturn(42);
        $accessFactory->create()->willReturn($accessManager);

        $values = $this->getValues();

        $accessConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $updater
            ->update($accessSupport, $values['converted_values'][0])
            ->shouldBeCalled();
        $updater
            ->update($accessManager, $values['converted_values'][1])
            ->shouldBeCalled();

        $validator
            ->validate($accessManager)
            ->willReturn($violationListManager);

        $validator
            ->validate($accessSupport)
            ->willReturn($violationListSupport);

        $this
            ->process($values['original_values'])
            ->shouldReturn([$accessSupport, $accessManager]);
    }

    protected function getValues()
    {
        return [
            'original_values' => [
                'category'   => 'videos',
                'view_items' => 'IT support,Manager',
                'edit_items' => 'IT support',
                'own_items'  => '',
            ],
            'converted_values' => [
                [
                    'category'   => 'videos',
                    'user_group'  => 'IT support',
                    'view_items' => true,
                    'edit_items' => true,
                    'own_items'  => false,
                ], [
                    'category'   => 'videos',
                    'user_group'  => 'Manager',
                    'view_items' => true,
                    'edit_items' => false,
                    'own_items'  => false,
                ]
            ]
        ];
    }
}
