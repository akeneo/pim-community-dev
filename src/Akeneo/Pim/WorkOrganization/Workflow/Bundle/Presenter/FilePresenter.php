<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Present two files information side by side
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class FilePresenter implements PresenterInterface
{
    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    public function __construct(
        FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return $attributeType === AttributeTypes::FILE;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $result = ['before' => null, 'after' => null];

        $originalMedia = $formerData;
        $changedMedia  = isset($change['data']) ? $this->fileInfoRepository->findOneByIdentifier($change['data']) : null;

        if (!$this->hasChanged($changedMedia, $originalMedia)) {
            return $result;
        }

        if (null !== $originalMedia && null !== $originalMedia->getKey() && null !== $originalMedia->getOriginalFilename()) {
            $result['before'] = $this->createFileElement($originalMedia->getKey(), $originalMedia->getOriginalFilename());
        }

        if (null !== $changedMedia && null !== $changedMedia->getKey() && null !== $changedMedia->getOriginalFilename()) {
            $result['after'] = $this->createFileElement($changedMedia->getKey(), $changedMedia->getOriginalFilename());
        }

        return $result;
    }

    /**
     * Create a file element
     *
     * @param string $fileKey
     * @param string $originalFilename
     *
     * @return array
     */
    protected function createFileElement($fileKey, $originalFilename)
    {
        return [
            'fileKey' => $fileKey,
            'originalFileName' => $originalFilename,
        ];
    }

    /**
     * Check diff between old and new file
     *
     * @param FileInfoInterface|null $changedMedia
     * @param FileInfoInterface|null $originalMedia
     * @return bool
     */
    protected function hasChanged(FileInfoInterface $changedMedia = null, FileInfoInterface $originalMedia = null)
    {
        $originalHash = null !== $originalMedia ? $originalMedia->getHash() : null;
        $changedHash  = null !== $changedMedia  ? $changedMedia->getHash()  : null;

        return $originalHash !== $changedHash;
    }
}
