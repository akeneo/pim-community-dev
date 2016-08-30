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
use Pim\Component\Catalog\AttributeTypes;
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
    public function present($data, array $change)
    {
        $result = ['before' => '', 'after' => ''];

        $media = $data->getMedia();
        if (!$this->hasChanged($change, $media)) {
            return $result;
        }

        if (null !== $media && null !== $media->getKey() && null !== $media->getOriginalFilename()) {
            $result['before'] = $this->createFileElement($media->getKey(), $media->getOriginalFilename());
        }

        if (isset($change['data']['filePath']) && isset($change['data']['originalFilename'])) {
            $result['after'] = $this->createFileElement(
                $change['data']['filePath'],
                $change['data']['originalFilename']
            );
        }

        return $result;
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

    /**
     * Check diff between old and new file
     *
     * @param array             $change
     * @param FileInfoInterface $fileInfo
     *
     * @return bool
     */
    protected function hasChanged(array $change, FileInfoInterface $fileInfo = null)
    {
        $dataHash = null !== $fileInfo ? $fileInfo->getHash() : null;
        $changeHash = isset($change['data']['hash']) ? $change['data']['hash'] : null;

        return $dataHash !== $changeHash;
    }
}
