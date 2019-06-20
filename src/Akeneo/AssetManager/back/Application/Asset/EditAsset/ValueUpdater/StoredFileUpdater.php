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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditStoredFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditUploadedFileValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueDataInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
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

    public function __invoke(Asset $asset, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the asset with the given command.');
        }

        $attribute = $command->attribute;
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();

        $fileData = $this->getFileData($asset, $command, $attribute, $channelReference, $localeReference);

        $asset->setValue(Value::create($attribute->getIdentifier(), $channelReference, $localeReference, $fileData));
    }

    private function getFileData(
        Asset $asset,
        $command,
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): ValueDataInterface {
        $valueKey = ValueKey::create($attribute->getIdentifier(), $channelReference, $localeReference);
        $existingValue = $asset->findValue($valueKey);

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
