<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Present images side by side
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        return $data instanceof AbstractProductValue
            && 'media' === $data->getAttribute()->getBackendType()
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
        if ($media = $data->getMedia()) {
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
     * @param string $source
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
     * @param array|Media $data
     *
     * @return boolean
     */
    protected function isImageMimeType($data)
    {
        switch (true) {
            case $data instanceof Media:
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
