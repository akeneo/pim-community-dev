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

use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Present images side by side
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
    public function supports($data, array $change)
    {
        return $data instanceof ProductValueInterface
            && array_key_exists('media', $change)
            && (
                $this->isImageMimeType($data->getMedia()) || $this->isImageMimeType($change['media'])
            );
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        $before = '';
        if (null !== $media = $data->getMedia()) {
            if (null !== $media->getFilename() && null !== $media->getOriginalFilename()) {
                $before = sprintf(
                    '<li class="base file">%s</li>',
                    $this->createImageElement($media->getFilename(), $media->getOriginalFilename())
                );
            }
        }

        $after = '';
        if (isset($change['media']['filename']) && isset($change['media']['originalFilename'])) {
            $after = sprintf(
                '<li class="changed file">%s</li>',
                $this->createImageElement($change['media']['filename'], $change['media']['originalFilename'])
            );
        }

        return sprintf('<ul class="diff">%s%s</ul>', $before, $after);
    }

    /**
     * Create an HTML Image element
     *
     * @param string $filename
     * @param string $title
     *
     * @return string
     */
    protected function createImageElement($filename, $title)
    {
        return sprintf(
            '<img src="%s" title="%s" />',
            $this->generator->generate(
                'pim_enrich_media_show',
                [
                    'filename' => $filename,
                    'filter' => 'thumbnail',
                ]
            ),
            $title
        );
    }

    /**
     * Check wether or not the given data represents an image
     *
     * @param array|ProductMedia $data
     *
     * @return bool
     */
    protected function isImageMimeType($data)
    {
        switch (true) {
            case $data instanceof ProductMedia:
                $mimeType = $data->getMimeType();
                break;
            case is_array($data) && isset($data['mimeType']):
                $mimeType = $data['mimeType'];
                break;
            default:
                $mimeType = null;
                break;
        }

        if (null === $mimeType) {
            return false;
        }

        return 0 === strpos($mimeType, 'image');
    }
}
