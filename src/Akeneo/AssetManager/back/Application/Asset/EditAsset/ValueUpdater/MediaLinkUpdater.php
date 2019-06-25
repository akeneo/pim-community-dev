<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkUpdater implements ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EditMediaLinkValueCommand;
    }

    public function __invoke(Asset $asset, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the asset with the given command.');
        }

        $attribute = $command->attribute->getIdentifier();
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();

        $text = MediaLinkData::createFromNormalize($command->mediaLink);

        $value = Value::create($attribute, $channelReference, $localeReference, $text);
        $asset->setValue($value);
    }
}
