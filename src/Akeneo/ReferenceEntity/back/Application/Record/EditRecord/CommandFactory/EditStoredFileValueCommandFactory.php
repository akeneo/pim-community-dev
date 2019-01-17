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

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Query\File\FindFileDataByFileKeyInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditStoredFileValueCommandFactory implements EditValueCommandFactoryInterface
{
    /** @var FindFileDataByFileKeyInterface */
    private $findFileData;

    public function __construct(FindFileDataByFileKeyInterface $findFileData)
    {
        $this->findFileData = $findFileData;
    }

    public function supports(AbstractAttribute $attribute, array $normalizedValue): bool
    {
        if (!key_exists('data', $normalizedValue)) {
            return false;
        }

        $filePath = is_array($normalizedValue['data']) ? ($normalizedValue['data']['filePath'] ?? null) :
                    (is_string($normalizedValue['data']) ? $normalizedValue['data'] : null);

        return $attribute instanceof ImageAttribute && null !== $filePath && '' !== $filePath;
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        $fileKey = $normalizedValue['data']['filePath'] ?? $normalizedValue['data'];
        $storedFile = is_string($fileKey) ? ($this->findFileData)($fileKey) : [];

        $command = new EditStoredFileValueCommand();
        $command->attribute = $attribute;
        $command->channel = $normalizedValue['channel'];
        $command->locale = $normalizedValue['locale'];
        $command->filePath = $fileKey;
        $command->originalFilename = $storedFile['originalFilename'] ?? null;
        $command->mimeType = $storedFile['mimeType'] ?? null;
        $command->extension = $storedFile['extension'] ?? null;
        $command->size = $storedFile['size'] ?? null;

        return $command;
    }
}
