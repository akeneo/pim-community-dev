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

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditStoredFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUploadedFileValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class StoredFileUpdater implements ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EditStoredFileValueCommand;
    }

    public function __invoke(Record $record, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the record with the given command.');
        }

        $attribute = $command->attribute;
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();

        $fileData = $this->getFileData($record, $command, $attribute, $channelReference, $localeReference);

        $record->setValue(Value::create($attribute->getIdentifier(), $channelReference, $localeReference, $fileData));
    }

    private function getFileData(
        Record $record,
        $command,
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): ValueDataInterface {
        $valueKey = ValueKey::create($attribute->getIdentifier(), $channelReference, $localeReference);
        $existingValue = $record->findValue($valueKey);

        if (null === $existingValue || $existingValue->getData()->getKey() !== $command->filePath) {
            $fileInfo = new FileInfo();
            $fileInfo->setKey($command->filePath);
            $fileInfo->setOriginalFilename($command->originalFilename);
            $fileInfo->setSize($command->size);
            $fileInfo->setMimeType($command->mimeType);
            $fileInfo->setExtension($command->extension);

            $fileData = FileData::createFromFileinfo($fileInfo);
        } else {
            $fileData = $existingValue->getData();
        }

        return $fileData;
    }
}
