<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Asset\Component\Upload\MassUpload;

use Akeneo\Asset\Bundle\Event\AssetEvent;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ChannelVariationsConfigurationInterface;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Tool\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Tool\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;
use Akeneo\Tool\Component\FileTransformer\Exception\NotApplicableTransformation\ImageHeightException;
use Akeneo\Tool\Component\FileTransformer\Exception\NotApplicableTransformation\ImageWidthException;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Upload\MassUpload\RetrieveAssetGenerationErrors;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class RetrieveAssetGenerationErrorsSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RetrieveAssetGenerationErrors::class);
    }

    function it_retrieves_invalid_options_transformation_exception(
        $translator,
        AssetInterface $asset,
        ChannelVariationsConfigurationInterface $configuration,
        ChannelInterface $channel
    ) {
        $configuration->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');

        $event = new AssetEvent($asset);
        $processedItems = new ProcessedItemList();
        $exception = InvalidOptionsTransformationException::general(
            new \Exception('Exception message'),
            'applied_transformation_code'
        );

        $processedItems->addItem(
            $configuration->getWrappedObject(),
            ProcessedItem::STATE_ERROR,
            $exception->getMessage(),
            $exception
        );
        $event->setProcessedList($processedItems);

        $translator->trans(
            'pimee_product_asset.enrich_variation.flash.transformation.invalid_options',
            ['%channel%' => 'ecommerce']
        )->willReturn('Impossible to generate the variation ecommerce due to invalid options.');

        $this
            ->fromEvent($event)
            ->shouldReturn(['Impossible to generate the variation ecommerce due to invalid options.']);
    }

    function it_retrieves_image_width_exception(
        $translator,
        AssetInterface $asset,
        ChannelVariationsConfigurationInterface $configuration,
        ChannelInterface $channel
    ) {
        $configuration->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');

        $event = new AssetEvent($asset);
        $processedItems = new ProcessedItemList();
        $exception = new ImageWidthException(
            'path/to/image',
            'applied_transformation_code'
        );

        $processedItems->addItem(
            $configuration->getWrappedObject(),
            ProcessedItem::STATE_ERROR,
            $exception->getMessage(),
            $exception
        );
        $event->setProcessedList($processedItems);

        $translator->trans(
            'pimee_product_asset.enrich_variation.flash.transformation.image_width_error',
            ['%channel%' => 'ecommerce']
        )->willReturn('Impossible to generate the variation ecommerce because the uploaded image has a width smaller than the one required by the transformation.');

        $this
            ->fromEvent($event)
            ->shouldReturn(['Impossible to generate the variation ecommerce because the uploaded image has a width smaller than the one required by the transformation.']);
    }

    function it_retrieves_image_height_exception(
        $translator,
        AssetInterface $asset,
        ChannelVariationsConfigurationInterface $configuration,
        ChannelInterface $channel
    ) {
        $configuration->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');

        $event = new AssetEvent($asset);
        $processedItems = new ProcessedItemList();
        $exception = new ImageHeightException(
            'path/to/image',
            'applied_transformation_code'
        );

        $processedItems->addItem(
            $configuration->getWrappedObject(),
            ProcessedItem::STATE_ERROR,
            $exception->getMessage(),
            $exception
        );
        $event->setProcessedList($processedItems);

        $translator->trans(
            'pimee_product_asset.enrich_variation.flash.transformation.image_height_error',
            ['%channel%' => 'ecommerce']
        )->willReturn('Impossible to generate the variation ecommerce because the uploaded image has a height smaller than the one required by the transformation.');

        $this
            ->fromEvent($event)
            ->shouldReturn(['Impossible to generate the variation ecommerce because the uploaded image has a height smaller than the one required by the transformation.']);
    }

    function it_retrieves_generic_transformation_exception(
        $translator,
        AssetInterface $asset,
        ChannelVariationsConfigurationInterface $configuration,
        ChannelInterface $channel
    ) {
        $configuration->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');

        $event = new AssetEvent($asset);
        $processedItems = new ProcessedItemList();
        $exception = new GenericTransformationException('A generic error message');

        $processedItems->addItem(
            $configuration->getWrappedObject(),
            ProcessedItem::STATE_ERROR,
            $exception->getMessage(),
            $exception
        );
        $event->setProcessedList($processedItems);

        $translator->trans(
            'pimee_product_asset.enrich_variation.flash.transformation.not_applicable',
            ['%channel%' => 'ecommerce']
        )->willReturn('Impossible to generate the variation ecommerce.');

        $this
            ->fromEvent($event)
            ->shouldReturn(['Impossible to generate the variation ecommerce.']);
    }

    function it_retrieves_non_registered_transformation_exception(
        $translator,
        AssetInterface $asset,
        ChannelVariationsConfigurationInterface $configuration,
        ChannelInterface $channel
    ) {
        $configuration->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');

        $event = new AssetEvent($asset);
        $processedItems = new ProcessedItemList();
        $exception = new NonRegisteredTransformationException('transformation_code', 'image/jpg');

        $processedItems->addItem(
            $configuration->getWrappedObject(),
            ProcessedItem::STATE_ERROR,
            $exception->getMessage(),
            $exception
        );
        $event->setProcessedList($processedItems);

        $translator->trans(
            'pimee_product_asset.enrich_variation.flash.transformation.non_registered',
            [
                '%channel%' => 'ecommerce',
                '%transformation%' => 'transformation_code',
                '%mimeType%' => 'image/jpg',
            ]
        )->willReturn('No transformation_code transformation registered for the mime type image/jpg. Impossible to generate the variation ecommerce.');

        $this
            ->fromEvent($event)
            ->shouldReturn(['No transformation_code transformation registered for the mime type image/jpg. Impossible to generate the variation ecommerce.']);
    }

    function it_retrieves_other_kind_of_exception(
        $translator,
        AssetInterface $asset,
        ChannelVariationsConfigurationInterface $configuration,
        ChannelInterface $channel
    ) {
        $configuration->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');

        $event = new AssetEvent($asset);
        $processedItems = new ProcessedItemList();
        $exception = new \Exception('A generic error message');

        $processedItems->addItem(
            $configuration->getWrappedObject(),
            ProcessedItem::STATE_ERROR,
            $exception->getMessage(),
            $exception
        );
        $event->setProcessedList($processedItems);

        $translator->trans(
            'pimee_product_asset.enrich_variation.flash.transformation.error',
            ['%channel%' => 'ecommerce']
        )->willReturn('Impossible to generate the variation ecommerce.');

        $this
            ->fromEvent($event)
            ->shouldReturn(['Impossible to generate the variation ecommerce.']);
    }
}
