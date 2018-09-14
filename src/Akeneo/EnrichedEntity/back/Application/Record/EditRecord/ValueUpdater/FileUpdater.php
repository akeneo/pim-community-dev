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
        $attribute = $command->attribute;
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();
        $data = (null !== $command->data) ?
            $this->getFileData($record, $command, $attribute, $channelReference, $localeReference) :
            EmptyData::create();
        
        $record->setValue(Value::create($attribute->getIdentifier(), $channelReference, $localeReference, $data));
    }

    private function getFileData(
        Record $record,
        AbstractEditValueCommand $command,
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): ValueDataInterface {
        $fileData = EmptyData::create();
        $valueKey = ValueKey::create($attribute->getIdentifier(), $channelReference, $localeReference);
        $existingFile = $record->getValue($valueKey);

        // If we want to update the file and it's not already in file storage, we store it
        if (
            null === $existingFile ||
            (
                is_array($command->data) &&
                key_exists('file_key', $command->data) &&
                $existingFile !== $command->data['file_key']
            )
        ) {
            $storedFile = $this->storeFile($command->data);
            $fileData = FileData::createFromFileinfo($storedFile);
        }

        return $fileData;
    }

    private function storeFile(array $image): FileInfoInterface
    {
        $rawFile = new \SplFileInfo($image['file_key']);
        try {
            $file = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
        } catch (FileTransferException | FileRemovalException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $file;
    }
}
