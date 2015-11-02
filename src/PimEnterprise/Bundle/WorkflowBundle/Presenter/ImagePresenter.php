<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Present images
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ImagePresenter implements PresenterInterface
{
    /** @var UrlGeneratorInterface */
    protected $generator;

    /**
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data)
    {
        return $data instanceof ProductValueInterface &&
            AttributeTypes::IMAGE === $data->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function presentOriginal($data, array $change)
    {
        $media = $data->getMedia();

        if (null === $media || null === $media->getKey() || null === $media->getOriginalFilename()) {
            return '';
        }

        return $this->createImageElement($media->getKey(), $media->getOriginalFilename());
    }

    /**
     * {@inheritdoc}
     */
    public function presentNew($data, array $change)
    {
        if (!isset($change['data']['originalFilename']) || !isset($change['data']['filePath'])) {
            return '';
        }

        return $this->createImageElement($change['data']['filePath'], $change['data']['originalFilename']);
    }

    /**
     * Create an HTML Image element
     *
     * @param string $filePath
     * @param string $title
     *
     * @return string
     */
    protected function createImageElement($filePath, $title)
    {
        return sprintf(
            '<img src="%s" title="%s" />',
            $this->generator->generate(
                'pim_enrich_media_show',
                [
                    'filename' => urlencode($filePath),
                    'filter'   => 'thumbnail',
                ]
            ),
            $title
        );
    }
}
