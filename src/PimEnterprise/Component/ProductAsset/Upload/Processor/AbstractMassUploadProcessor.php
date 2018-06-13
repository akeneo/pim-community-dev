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

namespace PimEnterprise\Component\ProductAsset\Upload\Processor;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageHeightException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageWidthException;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
abstract class AbstractMassUploadProcessor
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param AssetEvent $event
     *
     * @return string[]
     */
    protected function retrieveGenerationEventErrors(AssetEvent $event): array
    {
        $errors = [];
        $items = $event->getProcessedList();

        foreach ($items->getItemsInState(ProcessedItem::STATE_ERROR) as $item) {
            $parameters = ['%channel%' => $item->getItem()->getChannel()->getCode()];
            switch (true) {
                case $item->getException() instanceof InvalidOptionsTransformationException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.invalid_options';
                    break;
                case $item->getException() instanceof ImageWidthException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.image_width_error';
                    break;
                case $item->getException() instanceof ImageHeightException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.image_height_error';
                    break;
                case $item->getException() instanceof GenericTransformationException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.not_applicable';
                    break;
                case $item->getException() instanceof NonRegisteredTransformationException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.non_registered';
                    $parameters['%transformation%'] = $item->getException()->getTransformation();
                    $parameters['%mimeType%'] = $item->getException()->getMimeType();
                    break;
                default:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.error';
                    break;
            }
            $errors[] = $this->translator->trans(
                $template,
                $parameters
            );
        }

        return $errors;
    }
}
