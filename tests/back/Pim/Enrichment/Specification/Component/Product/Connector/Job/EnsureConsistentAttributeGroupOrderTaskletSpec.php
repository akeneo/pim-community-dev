<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\EnsureConsistentAttributeGroupOrderTasklet;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EnsureConsistentAttributeGroupOrderTaskletSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $attributeGroupRepository,
            $attributeGroupReader,
            $attributeGroupSaver,
            $findAttributeGroupOrdersEqualOrSuperiorTo,
            $validator
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(EnsureConsistentAttributeGroupOrderTasklet::class);
    }

    function it_sets_consistent_sort_order_on_an_attribute_group_by_setting_next_available_sort_order(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        StepExecution $stepExecution,
        ValidatorInterface $validator,
        AttributeGroup $attributeGroup
    ) {
        $this->setStepExecution($stepExecution);
        $attributeGroup->getSortOrder()->willReturn('10');

        $attributeGroupReader->read()->willReturn(['code' => 'marketing'], null);
        $attributeGroupRepository->findOneByIdentifier('marketing')->willReturn($attributeGroup);

        $findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup)->willReturn([
            '10', '12'
        ]);

        $attributeGroup->setSortOrder(11)->shouldBeCalled();
        $validator->validate($attributeGroup)->willReturn(new ConstraintViolationList());

        $attributeGroupSaver->save($attributeGroup);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->execute($attributeGroup);
    }

    function it_sets_consistent_sort_order_on_an_attribute_group_by_setting_an_available_sort_order(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        StepExecution $stepExecution,
        ValidatorInterface $validator,
        AttributeGroup $attributeGroup
    ) {
        $this->setStepExecution($stepExecution);
        $attributeGroup->getSortOrder()->willReturn('10');

        $attributeGroupReader->read()->willReturn(['code' => 'marketing'], null);
        $attributeGroupRepository->findOneByIdentifier('marketing')->willReturn($attributeGroup);

        $findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup)->willReturn([
            '10', '11', '12'
        ]);

        $attributeGroup->setSortOrder(13)->shouldBeCalled();
        $validator->validate($attributeGroup)->willReturn(new ConstraintViolationList());

        $attributeGroupSaver->save($attributeGroup);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->execute($attributeGroup);
    }

    function it_sets_consistent_sort_order_on_an_attribute_group_by_doing_nothing_if_sort_order_is_available(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        StepExecution $stepExecution,
        AttributeGroup $attributeGroup
    ) {
        $this->setStepExecution($stepExecution);
        $attributeGroup->getSortOrder()->willReturn('11');

        $attributeGroupReader->read()->willReturn(['code' => 'marketing'], null);
        $attributeGroupRepository->findOneByIdentifier('marketing')->willReturn($attributeGroup);

        $findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup)->willReturn([
            '10', '12'
        ]);

        $attributeGroupSaver->save($attributeGroup)->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->execute($attributeGroup);
    }

    function it_sets_consistent_sort_order_on_an_attribute_group_by_doing_nothing_if_no_conflict(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        StepExecution $stepExecution,
        AttributeGroup $attributeGroup
    ) {
        $this->setStepExecution($stepExecution);
        $attributeGroup->getSortOrder()->willReturn('11');

        $attributeGroupReader->read()->willReturn(['code' => 'marketing'], null);
        $attributeGroupRepository->findOneByIdentifier('marketing')->willReturn($attributeGroup);

        $findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup)->willReturn([]);

        $attributeGroupSaver->save($attributeGroup)->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->execute($attributeGroup);
    }

    function it_skips_attribute_group_if_there_is_any_validation_error(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        StepExecution $stepExecution,
        AttributeGroup $attributeGroup,
        ValidatorInterface $validator,
        ConstraintViolationList $violationList
    ) {
        $this->setStepExecution($stepExecution);
        $attributeGroup->getSortOrder()->willReturn('11');

        $attributeGroupReader->read()->willReturn(['code' => 'marketing'], null);
        $attributeGroupRepository->findOneByIdentifier('marketing')->willReturn($attributeGroup);

        $findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup)->willReturn([
            '11', '12'
        ]);

        $attributeGroup->setSortOrder(13)->shouldBeCalled();
        $validator->validate($attributeGroup)->willReturn($violationList);
        $violationList->count()->willReturn(1);

        $attributeGroupSaver->save($attributeGroup)->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->execute($attributeGroup);
    }
}
