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
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUploadedFileValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UploadedFileUpdater implements ValueUpdaterInterface
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
        return $command instanceof EditUploadedFileValueCommand;
    }

    /**
     * @throws FileRemovalException
     * @throws FileTransferException
     */
    public function __invoke(Record $record, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the record with the given command.');
        }

        /** @var EditUploadedFileValueCommand $command */
        $attribute = $command->attribute;
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();

        $fileData = $this->storeFile($command);

        $record->setValue(Value::create($attribute->getIdentifier(), $channelReference, $localeReference, $fileData));
    }

    /**
     * @throws FileRemovalException
     * @throws FileTransferException
     */
    private function storeFile(EditUploadedFileValueCommand $command): ValueDataInterface
    {
        $rawFile = new \SplFileInfo($command->filePath);
        $storedFile = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);

        $fileData = FileData::createFromFileinfo($storedFile);

        return $fileData;
    }
}
