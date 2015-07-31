<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use League\Flysystem\MountManager;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product file publisher
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FilePublisher implements PublisherInterface
{
    /** @var RawFileFetcherInterface */
    protected $rawFileFetcher;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var MountManager */
    protected $mountManager;

    /**
     * @param RawFileFetcherInterface $rawFileFetcher
     * @param RawFileStorerInterface  $rawFileStorer
     * @param MountManager            $mountManager
     */
    public function __construct(
        RawFileFetcherInterface $rawFileFetcher,
        RawFileStorerInterface $rawFileStorer,
        MountManager $mountManager
    ) {
        $this->rawFileFetcher = $rawFileFetcher;
        $this->rawFileStorer  = $rawFileStorer;
        $this->mountManager   = $mountManager;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($file, array $options = [])
    {
        if (!isset($options['product'])) {
            throw new \LogicException('Original product must be known');
        }

        if (!isset($options['value'])) {
            throw new \LogicException('Original product value must be known');
        }

        $value = $options['value'];

        if (null !== $value->getMedia() && null !== $value->getMedia()->getKey()) {
            //TODO: remove the hardcoded 'storage'
            $filesystem = $this->mountManager->getFilesystem('storage');

            $rawFile = $this->rawFileFetcher->fetch($file->getKey(), $filesystem);
            $file = $this->rawFileStorer->store($rawFile, 'storage', false);
        }

        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof FileInterface;
    }
}
