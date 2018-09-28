<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Connector\Processor\MassEdit\Asset\ClassifyAssetsProcessor;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\Security\Attributes;
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
