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

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditFileValueCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\FileData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FileUpdater implements ValueUpdaterInterface
{
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';

    /** @var FileStorerInterface  */
    private $storer;

    public function __construct(FileStorerInterface $storer)
    {
        $this->storer = $storer;
    }

    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EditFileValueCommand;
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
        $fileData = null !== $command->filePath && '' !== $command->filePath ?
            $this->getFileData($record, $command, $attribute, $channelReference, $localeReference) :
            EmptyData::create();
        
        $record->setValue(Value::create($attribute->getIdentifier(), $channelReference, $localeReference, $fileData));
    }

    private function getFileData(
        Record $record,
        EditFileValueCommand $command,
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): ValueDataInterface {
        $fileData = EmptyData::create();
        $valueKey = ValueKey::create($attribute->getIdentifier(), $channelReference, $localeReference);
        $existingValue = $record->findValue($valueKey);

        // If we want to update the file and it's not already in file storage, we store it
        if (null === $existingValue || $existingValue->getData()->getKey() !== $command->filePath) {
            $storedFile = $this->storeFile($command->filePath);
            $fileData = FileData::createFromFileinfo($storedFile);
        }

        return $fileData;
    }

    private function storeFile(string $fileKey): FileInfoInterface
    {
        $rawFile = new \SplFileInfo($fileKey);
        try {
            $file = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
        } catch (FileTransferException | FileRemovalException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage(), $exception);
        }

        return $file;
    }
}
