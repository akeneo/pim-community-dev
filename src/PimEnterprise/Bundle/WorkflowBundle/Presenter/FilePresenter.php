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
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Present two files information side by side
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class FilePresenter implements PresenterInterface
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
        return $data instanceof ProductValueInterface
            && AttributeTypes::FILE === $data->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        $media = $data->getMedia();
        if (!$this->isDiff($change, $media)) {
            return '';
        }

        $before = '';
        if (null !== $media) {
            if (null !== $media->getFilename() && null !== $media->getOriginalFilename()) {
                $before = sprintf(
                    '<li class="base file">%s</li>',
                    $this->createFileElement($media->getFilename(), $media->getOriginalFilename())
                );
            }
        }

        $after = '';
        if (isset($change['data']['originalFilename']) && isset($change['data']['filename'])) {
            $after = sprintf(
                '<li class="changed file">%s</li>',
                $this->createFileElement($change['data']['filename'], $change['data']['originalFilename'])
            );
        }

        return sprintf('<ul class="diff">%s%s</ul>', $before, $after);
    }

    /**
     * Create a file element
     *
     * @param string $filename
     * @param string $originalFilename
     *
     * @return string
     */
    protected function createFileElement($filename, $originalFilename)
    {
        return sprintf(
            '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
            $this->generator->generate('pim_enrich_media_show', ['filename' => $filename]),
            $originalFilename
        );
    }

    /**
     * Check diff between old and new file
     *
     * @param array                 $change
     * @param ProductMediaInterface $media
     *
     * @return bool
     */
    protected function isDiff(array $change, ProductMediaInterface $media = null)
    {
        if (null !== $media && null !== $media->getFilename()) {
            $data = sha1_file($this->generator->generate('pim_enrich_media_show', [
                'filename' => $media->getFilename()
            ], UrlGeneratorInterface::ABSOLUTE_URL));
        } else {
            $data = null;
        }

        $change = isset($change['data']['filePath']) ? sha1_file($change['data']['filePath']) : null;

        return $data !== $change;
    }
}
