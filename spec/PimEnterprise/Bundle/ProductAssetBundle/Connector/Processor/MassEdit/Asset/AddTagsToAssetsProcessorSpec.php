<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Classification\Repository\TagRepositoryInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddTagsToAssetsProcessorSpec extends ObjectBehavior
{
    function let(
        TagRepositoryInterface $repository,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductAssetBundle\Connector\Processor\MassEdit\Asset\AddTagsToAssetsProcessor');
    }

    function it_is_a_item_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_adds_existing_tags_to_an_asset(
        $stepExecution,
        $repository,
        $validator,
        AssetInterface $asset,
        JobParameters $jobParameters,
        TagInterface $foo,
        TagInterface $bar
    ) {
        $actions = [
            [
                'field' => 'tags',
                'value' => [
                    'foo',
                    'bar',
                ],
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn($actions);
        $repository->findOneByIdentifier('foo')->willReturn($foo);
        $repository->findOneByIdentifier('bar')->willReturn($bar);

        $asset->addTag($foo)->shouldBeCalled();
        $asset->addTag($bar)->shouldBeCalled();
        $validator->validate($asset)->willReturn(new ConstraintViolationList([]));

        $this->process($asset);
    }

    function it_does_not_add_non_existing_tags_to_an_asset(
        $stepExecution,
        $repository,
        $validator,
        AssetInterface $asset,
        JobParameters $jobParameters
    ) {
        $actions = [
            [
                'field' => 'tags',
                'value' => ['foo'],
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn($actions);
        $repository->findOneByIdentifier('foo')->willReturn(null);

        $asset->addTag()->shouldNotBeCalled();
        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.add-tags-to-assets.message.error',
            [],
            Argument::type('Akeneo\Component\Batch\Item\InvalidItemInterface')
        )->shouldBeCalled();
        $validator->validate($asset)->willReturn(new ConstraintViolationList([]));

        $this->process($asset);
    }
}
