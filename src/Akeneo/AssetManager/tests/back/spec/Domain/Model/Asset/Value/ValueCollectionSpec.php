<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class ValueCollectionSpec extends ObjectBehavior
{
    function let() {
        $file = new FileInfo();
        $file->setKey('/a/file/key');
        $file->setOriginalFilename('my_file.png');
        $file->setSize(1024);
        $file->setMimeType('image/png');
        $file->setExtension('png');

        $this->beConstructedThrough('fromValues', [
            [
                'name_designer_fingerprint' => Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    TextData::fromString('Philippe Starck')
                ),
                'image_designer_fingerprintmobilefr_FR' => Value::create(
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    FileData::createFromFileinfo(
                        $file,
                        \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000')
                    )
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
            'name_designer_fingerprint' => [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'Philippe Starck',
            ],
            'image_designer_fingerprint_mobile_fr_FR' => [
                'attribute' => 'image_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => [
                    'filePath' => '/a/file/key',
                    'originalFilename' => 'my_file.png',
                    'size' => 1024,
                    'mimeType' => 'image/png',
                    'extension' => 'png',
                    'updatedAt' => '2019-11-22T15:16:21+0000',
                ],
            ],
        ]);
    }

    function it_returns_a_new_instance_of_itself_when_replacing_an_existing_value_with_a_new_one()
    {
        $newValueCollection = $this->setValue(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                TextData::fromString('Updated name')
            )
        );
        $newValueCollection->normalize()->shouldReturn([
            'name_designer_fingerprint' => [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'Updated name',
            ],
            'image_designer_fingerprint_mobile_fr_FR' => [
                'attribute' => 'image_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => [
                    'filePath'              => '/a/file/key',
                    'originalFilename' => 'my_file.png',
                    'size' => 1024,
                    'mimeType' => 'image/png',
                    'extension' => 'png',
                    'updatedAt' => '2019-11-22T15:16:21+0000',
                ],
            ],
        ]);
    }

    function it_adds_an_inexisting_value_to_the_collection()
    {
        $values = $this->setValue(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('name for fr_FR and mobile')
            )
        );
        $values->normalize()->shouldReturn([
            'name_designer_fingerprint' => [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'Philippe Starck',
            ],
            'image_designer_fingerprint_mobile_fr_FR' => [
                'attribute' => 'image_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => [
                    'filePath'              => '/a/file/key',
                    'originalFilename' => 'my_file.png',
                    'size' => 1024,
                    'mimeType' => 'image/png',
                    'extension' => 'png',
                    'updatedAt' => '2019-11-22T15:16:21+0000',
                ],
            ],
            'name_designer_fingerprint_mobile_fr_FR' => [
                'attribute' => 'name_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => 'name for fr_FR and mobile',
            ],
        ]);
    }

    function it_cannot_instanciate_with_any_other_objects()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromValues', [[1]]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromValues', [[new \StdClass()]]);
    }

    function it_finds_a_value_for_a_given_value_key()
    {
        $attributeIdentifier = AttributeIdentifier::fromString('name_designer_fingerprint');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();

        $value = $this->findValue(ValueKey::create($attributeIdentifier, $channelReference, $localeReference));

        $value->getAttributeIdentifier()->equals($attributeIdentifier)->shouldBeEqualTo(true);
        $value->getChannelReference()->equals($channelReference)->shouldBeEqualTo(true);
        $value->getLocaleReference()->equals($localeReference)->shouldBeEqualTo(true);
        $value->getData()->normalize()->shouldBeEqualTo('Philippe Starck');
    }

    function it_returns_null_if_it_does_not_find_a_value_for_a_given_value_key()
    {
        $attributeIdentifier = AttributeIdentifier::fromString('unknown_attribute');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();

        $this->findValue(ValueKey::create($attributeIdentifier, $channelReference, $localeReference))
            ->shouldBeNull();
    }

    function it_filters_the_values()
    {
        $this->filter(fn(Value $value) => false)->normalize()->shouldReturn([]);
        $this->filter(fn(Value $value) => !$value->getChannelReference()->isEmpty())->findValue(ValueKey::createFromNormalized('image_designer_fingerprint_mobile_fr_FR'))
            ->shouldNotBeNull();
    }

    function it_checks_if_value_already_exist()
    {
        $this->hasValue(Value::create(
            AttributeIdentifier::fromString('another_value'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            EmptyData::create()
        ))->shouldReturn(true);

        $this->hasValue(Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Philippe Starck')
        ))->shouldReturn(true);

        $this->hasValue(Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Philippe Starcks')
        ))->shouldReturn(false);

        $this->hasValue(Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::createFromNormalized('fr_FR'),
            TextData::fromString('Philippe Starcks')
        ))->shouldReturn(false);

        $this->hasValue(Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::createFromNormalized('e-commerce'),
            LocaleReference::noReference(),
            TextData::fromString('Philippe Starcks')
        ))->shouldReturn(false);
    }

    function it_returns_the_count_of_values()
    {
        $this->count()->shouldReturn(2);
    }
}
