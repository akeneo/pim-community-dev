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

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
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
    public function presentOriginal($data, array $change)
    {
        $media = $data->getMedia();

        if (null === $media || null === $media->getKey() || null === $media->getOriginalFilename()) {
            return '';
        }

        return $this->createFileElement($media->getKey(), $media->getOriginalFilename());
    }

    /**
     * {@inheritdoc}
     */
    public function presentNew($data, array $change)
    {
        if (!isset($change['data']['originalFilename']) || !isset($change['data']['filePath'])) {
            return '';
        }

        return $this->createFileElement($change['data']['filePath'], $change['data']['originalFilename']);
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
            $this->generator->generate('pim_enrich_media_show', ['filename' => urlencode($filename)]),
            $originalFilename
        );
    }
}
