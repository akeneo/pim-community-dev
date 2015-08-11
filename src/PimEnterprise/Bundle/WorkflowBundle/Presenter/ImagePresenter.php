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

use Akeneo\Component\FileStorage\Model\FileInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
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
    public function supports($data)
    {
        return $data instanceof ProductValueInterface &&
            AttributeTypes::IMAGE === $data->getAttribute()->getAttributeType();
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
        if (null !== $media && null !== $media->getKey() && null !== $media->getOriginalFilename()) {
            $before = sprintf(
                '<li class="base file">%s</li>',
                $this->createImageElement($media->getKey(), $media->getOriginalFilename())
            );
        }

        $after = '';
        if (isset($change['data']['filePath']) && isset($change['data']['originalFilename'])) {
            $after = sprintf(
                '<li class="changed file">%s</li>',
                $this->createImageElement($change['data']['filePath'], $change['data']['originalFilename'])
            );
        }

        return sprintf('<ul class="diff">%s%s</ul>', $before, $after);
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

    /**
     * Check diff between old and new file
     *
     * @param array         $change
     * @param FileInterface $file
     *
     * @return bool
     */
    protected function isDiff(array $change, FileInterface $file = null)
    {
        $dataHash   = null !== $file ? $file->getHash() : null;
        $changeHash = isset($change['data']['hash']) ? $change['data']['hash'] : null;

        return $dataHash !== $changeHash;
    }
}
