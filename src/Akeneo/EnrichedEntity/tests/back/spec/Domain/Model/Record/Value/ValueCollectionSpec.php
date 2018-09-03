<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference\NoChannel;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\FileData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference\NoLocale;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class ValueCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $file = new FileInfo();
        $file->setKey('/a/file/key');
        $file->setOriginalFilename('my_file.png');

        $this->beConstructedThrough('fromValues', [
            [
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    NoChannel::create(),
                    NoLocale::create(),
                    TextData::fromString('Philippe Starck')
                ),
                Value::create(
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
                    ChannelIdentifier::fromCode('mobile'),
                    LocaleIdentifier::fromCode('fr_FR'),
                    FileData::createFromFileinfo($file)
                ),
            ],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollection::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'Philippe Starck',
            ],
            [
                'attribute' => 'image_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => [
                    'key'              => '/a/file/key',
                    'originalFilename' => 'my_file.png',
                ],
            ],
        ]);
    }

    function it_returns_a_new_instance_of_itself_when_replacing_an_existing_value_with_a_new_one()
    {
        $newValueCollection = $this->setValue(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                NoChannel::create(),
                NoLocale::create(),
                TextData::fromString('Updated name')
            )
        );
        $newValueCollection->normalize()->shouldReturn([
            [
                'attribute' => 'image_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => [
                    'key'              => '/a/file/key',
                    'originalFilename' => 'my_file.png',
                ],
            ],
            [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'Updated name',
            ],
        ]);
    }

    function it_adds_an_inexisting_value_to_the_collection()
    {
        $values = $this->setValue(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                ChannelIdentifier::fromCode('mobile'),
                LocaleIdentifier::fromCode('fr_FR'),
                TextData::fromString('name for fr_FR and mobile')
            )
        );
        $values->normalize()->shouldReturn([
            [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'Philippe Starck',
            ],
            [
                'attribute' => 'image_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => [
                    'key'              => '/a/file/key',
                    'originalFilename' => 'my_file.png',
                ],
            ],
            [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => 'name for fr_FR and mobile',
            ],
        ]);
    }
}
