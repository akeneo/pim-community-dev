<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCollectionValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionUpdater implements ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EditRecordCollectionValueCommand;
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
        $linkedRecordCollection = RecordCollectionData::createFromNormalize($command->recordCodes);

        $value = Value::create($attribute, $channelReference, $localeReference, $linkedRecordCollection);
        $record->setValue($value);
    }
}
