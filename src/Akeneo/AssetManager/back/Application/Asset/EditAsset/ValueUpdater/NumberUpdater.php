<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditNumberValueCommand;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class NumberUpdater implements ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EditNumberValueCommand;
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

        $numberValue = NumberData::createFromNormalize($command->number);

        $value = Value::create($attribute, $channelReference, $localeReference, $numberValue);
        $asset->setValue($value);
    }
}
