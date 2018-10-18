<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset\ClassifyAssetsProcessor;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClassifyAssetsProcessorSpec extends ObjectBehavior
{
    function let(
        StepExecution $stepExecution,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($updater, $validator, $authorizationChecker);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ClassifyAssetsProcessor::class);
    }

    function it_is_a_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_classifies_an_asset(
        $stepExecution,
        $updater,
        $validator,
        $authorizationChecker,
        AssetInterface $asset,
        JobParameters $jobParameters
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(true);

        $actions = [
            [
                'field' => 'categories',
                'value' => ['promo', 'web'],
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn($actions);
        $validator->validate($asset)->willReturn(new ConstraintViolationList([]));

        $updater->update($asset, ['categories' => ['promo', 'web']])->shouldBeCalled();

        $this->process($asset);
    }

    function it_does_not_classify_an_asset_not_editable_by_the_user(
        $stepExecution,
        $updater,
        $authorizationChecker,
        AssetInterface $asset
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(false);

        $asset->getCode()->willReturn('akene');
        $stepExecution->addWarning(
            'pimee_product_asset.not_editable',
            ['%code%' => 'akene'],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_assets')->shouldBeCalled();

        $updater->update(Argument::cetera())->shouldNotBeCalled();

        $this->process($asset);
    }
}
