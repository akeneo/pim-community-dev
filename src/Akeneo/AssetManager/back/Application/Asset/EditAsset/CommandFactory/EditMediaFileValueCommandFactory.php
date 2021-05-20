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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Query\File\FindFileDataByFileKeyInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditMediaFileValueCommandFactory implements EditValueCommandFactoryInterface
{
    private FindFileDataByFileKeyInterface $findFileData;

    public function __construct(FindFileDataByFileKeyInterface $findFileData)
    {
        $this->findFileData = $findFileData;
    }

    public function supports(AbstractAttribute $attribute, array $normalizedValue): bool
    {
        if (!array_key_exists('data', $normalizedValue)) {
            return false;
        }

        $filePath = is_array($normalizedValue['data']) ? ($normalizedValue['data']['filePath'] ?? null) :
                    (is_string($normalizedValue['data']) ? $normalizedValue['data'] : null);

        return $attribute instanceof MediaFileAttribute && null !== $filePath && '' !== $filePath;
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        $fileKey = $normalizedValue['data']['filePath'] ?? $normalizedValue['data'];
        $mediaFile = is_string($fileKey) ? $this->findFileData->find($fileKey) : [];
        $updatedAt = $normalizedValue['data']['updatedAt'] ?? ($mediaFile['updatedAt'] ?? null);
        return new EditMediaFileValueCommand(
            $attribute,
            $normalizedValue['channel'],
            $normalizedValue['locale'],
            $fileKey,
            $mediaFile['originalFilename'] ?? null,
            $mediaFile['size'] ?? null,
            $mediaFile['mimeType'] ?? null,
            $mediaFile['extension'] ?? null,
            // TODO Move all command factories into Infra folder and split for each adapter (UI, API, external API)
            $updatedAt ?? (new \DateTimeImmutable())->format(\DateTimeInterface::ISO8601)
        );
    }
}
