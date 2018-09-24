<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextUpdater implements ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EditTextValueCommand;
    }

    public function __invoke(Record $record, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the record with the given command.');
        }

        $attribute = $command->attribute->getIdentifier();
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();
        $text = (null !== $command->text) ?
            TextData::createFromNormalize($command->text) :
            EmptyData::create();

        $value = Value::create($attribute, $channelReference, $localeReference, $text);
        $record->setValue($value);
    }
}
