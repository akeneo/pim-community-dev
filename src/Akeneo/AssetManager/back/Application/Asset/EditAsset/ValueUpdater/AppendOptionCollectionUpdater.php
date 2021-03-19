<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AppendOptionCollectionValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

/**
 * Asset updater responsible for append values of "asset collection" on a asset.
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class AppendOptionCollectionUpdater implements ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof AppendOptionCollectionValueCommand;
    }

    public function __invoke(Asset $asset, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the asset with the given command.');
        }

        if (empty($command->optionCodes)) return;

        /** @var AppendOptionCollectionValueCommand $command */
        $attributeIdentifier = $command->attribute->getIdentifier();
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();

        $valueKey = ValueKey::create($attributeIdentifier, $channelReference, $localeReference);
        $existingValue = $asset->findValue($valueKey);
        $existingOptionCodes = $existingValue ? $existingValue->getData()->normalize() : [];
        $newData = array_values(array_unique(array_merge($existingOptionCodes, $command->optionCodes)));

        $options = OptionCollectionData::createFromNormalize($newData);

        $value = Value::create($attributeIdentifier, $channelReference, $localeReference, $options);
        $asset->setValue($value);
    }
}
