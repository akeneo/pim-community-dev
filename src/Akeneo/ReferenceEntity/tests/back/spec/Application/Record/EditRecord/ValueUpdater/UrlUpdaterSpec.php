<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditStoredFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUrlValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\UrlUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\UrlData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UrlUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UrlUpdater::class);
    }

    function it_only_supports_edit_url_value_command(
        EditStoredFileValueCommand $editStoredFileValueCommand,
        EditUrlValueCommand $editTextValueCommand
    ) {
        $this->supports($editStoredFileValueCommand)->shouldReturn(false);
        $this->supports($editTextValueCommand)->shouldReturn(true);
    }

    function it_edits_the_url_value_of_a_record(Record $record) {
        $urlAttribute = $this->urlAttribute();

        $editTextValueCommand = new EditUrlValueCommand(
            $urlAttribute,
            'ecommerce',
            'fr_FR',
            'house_255121'
        );
        $value = Value::create(
            $editTextValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editTextValueCommand->channel),
            LocaleReference::createfromNormalized($editTextValueCommand->locale),
            UrlData::createFromNormalize($editTextValueCommand->url)
        );

        $this->__invoke($record, $editTextValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(
        Record $record,
        EditStoredFileValueCommand $editStoredFileValueCommand
    ) {
        $this->supports($editStoredFileValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$record, $editStoredFileValueCommand]);
    }

    private function urlAttribute(): UrlAttribute
    {
        return UrlAttribute::create(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            Prefix::empty(),
            Suffix::empty(),
            MediaType::fromString('image')
        );
    }
}
