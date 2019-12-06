<?php

namespace Specification\Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset\AddTagsToAssetsProcessor;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\Tag;
use Akeneo\Asset\Component\Model\TagInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\Repository\TagRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddTagsToAssetsProcessorSpec extends ObjectBehavior
{
    function let(
        TagRepositoryInterface $repository,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        AuthorizationCheckerInterface $authorizationChecker,
        ObjectManager $objectManager
    ) {
        $this->beConstructedWith($repository, $validator, $authorizationChecker, $objectManager);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddTagsToAssetsProcessor::class);
    }

    function it_is_a_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_adds_existing_tags_to_an_asset(
        $stepExecution,
        $repository,
        $validator,
        $authorizationChecker,
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

        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(true);

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
        $authorizationChecker,
        $objectManager,
        AssetInterface $asset1,
        AssetInterface $asset2,
        JobParameters $jobParameters
    ) {
        $actions = [
            [
                'field' => 'tags',
                'value' => ['foo'],
            ],
        ];

        $authorizationChecker->isGranted(Attributes::EDIT, $asset1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $asset2)->willReturn(true);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn($actions);
        $repository->findOneByIdentifier('foo')->shouldBeCalledOnce()->willReturn(null);

        $tag = new Tag();
        $tag->setCode('foo');
        $asset1->addTag($tag)->shouldBeCalled();
        $asset2->addTag($tag)->shouldBeCalled();
        $validator->validate($asset1)->willReturn(new ConstraintViolationList([]));
        $validator->validate($asset2)->willReturn(new ConstraintViolationList([]));

        $objectManager->persist($tag)->shouldBeCalledOnce();

        $this->process($asset1);
        $this->process($asset2);
    }

    function it_does_not_add_tags_to_an_asset_non_editable_by_the_user(
        $stepExecution,
        $authorizationChecker,
        AssetInterface $asset
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(false);

        $asset->getCode()->willReturn('akene');
        $asset->addTag()->shouldNotBeCalled();

        $stepExecution->addWarning(
            'pimee_product_asset.not_editable',
            ['%code%' => 'akene'],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_assets')->shouldBeCalled();

        $this->process($asset);
    }
}
