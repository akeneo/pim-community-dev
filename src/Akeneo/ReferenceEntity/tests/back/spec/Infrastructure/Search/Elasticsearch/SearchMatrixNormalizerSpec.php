<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\SearchMatrixNormalizer;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\SqlFindActivatedLocalesPerChannels;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchMatrixNormalizerSpec extends ObjectBehavior
{
    function let(SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels)
    {
        $this->beConstructedWith($findActivatedLocalesPerChannels);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchMatrixNormalizer::class);
    }

    function it_normalizes_the_code_in_the_matrix_field(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        )->shouldReturn([
            'ecommerce' => [
                'fr_FR' => 'stark',
                'en_US' => 'stark',
            ],
            'mobile'    => [
                'fr_FR' => 'stark',
                'en_US' => 'stark',
            ],
        ]);
    }

    function it_normalizes_the_labels_in_the_search_field_if_there_are_any(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                ['fr_FR' => 'Un talentueux designer', 'en_US' => 'A wonderful designer'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        )->shouldReturn([
            'ecommerce' => [
                'fr_FR' => 'stark Un talentueux designer',
                'en_US' => 'stark A wonderful designer',
            ],
            'mobile'    => [
                'fr_FR' => 'stark Un talentueux designer',
                'en_US' => 'stark A wonderful designer',
            ],
        ]);
    }

    function it_generates_search_matrix_for_non_scopable_non_localizable_text_values(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([
                        Value::create(
                            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString('.not value per channel / not value per locale.')
                        ),
                    ]
                )
            )
        )->shouldReturn([
                'ecommerce' => [
                    'fr_FR' => 'stark .not value per channel / not value per locale.',
                    'en_US' => 'stark .not value per channel / not value per locale.',
                ],
                'mobile'    => [
                    'fr_FR' => 'stark .not value per channel / not value per locale.',
                    'en_US' => 'stark .not value per channel / not value per locale.',
                ],
            ]
        );
    }

    function it_generates_search_matrix_for_scopable_and_localizable_text_values(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([
                        // one value per channel and one value per locale
                        Value::create(
                            AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                            TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
                        ),
                    ]
                )
            )
        )->shouldReturn([
                'ecommerce' => [
                    'fr_FR' => 'stark .one value per channel ecommerce / one value per locale fr_FR.',
                    'en_US' => 'stark',
                ],
                'mobile'    => [
                    'fr_FR' => 'stark',
                    'en_US' => 'stark',
                ],
            ]
        );
    }

    function it_generates_search_matrix_for_scopable_text_values(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([
                        Value::create(
                            AttributeIdentifier::create('designer', 'country', 'fingerprint'),
                            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                            LocaleReference::noReference(),
                            TextData::fromString('.one value per channel mobile.')
                        ),
                    ]
                )
            )
        )->shouldReturn([
                'ecommerce' => [
                    'fr_FR' => 'stark',
                    'en_US' => 'stark',
                ],
                'mobile'    => [
                    'fr_FR' => 'stark .one value per channel mobile.',
                    'en_US' => 'stark .one value per channel mobile.',
                ],
            ]
        );
    }

    function it_generates_search_matrix_for_localizable_text_values(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([
                        Value::create(
                            AttributeIdentifier::create('designer', 'bio', 'fingerprint'),
                            ChannelReference::noReference(),
                            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                            TextData::fromString('.one value per locale fr_FR.')
                        ),
                    ]
                )
            )
        )->shouldReturn([
                'ecommerce' => [
                    'fr_FR' => 'stark .one value per locale fr_FR.',
                    'en_US' => 'stark',
                ],
                'mobile'    => [
                    'fr_FR' => 'stark .one value per locale fr_FR.',
                    'en_US' => 'stark',
                ],
            ]
        );
    }

    function it_generates_search_matrix_text_values_only(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels
    ) {
        $findActivatedLocalesPerChannels->__invoke()->willReturn(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['fr_FR', 'en_US'],
            ]
        );

        $fileInfo = new FileInfo();
        $fileInfo->setOriginalFilename('My file.jpeg');
        $fileInfo->setKey('/an/image/key');

        $this->generate(
            Record::create(
                RecordIdentifier::create('designer', 'stark', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([
                        // one value per channel and one value per locale
                        Value::create(
                            AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                            TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
                        ),

                        // Image
                        Value::create(
                            AttributeIdentifier::create('designer', 'country', 'fingerprint'),
                            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                            FileData::createFromFileinfo($fileInfo)
                        ),

                        // Empty
                        Value::create(
                            AttributeIdentifier::create('designer', 'bio', 'fingerprint'),
                            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                            EmptyData::create()
                        ),

                    ]
                )
            ))->shouldReturn([
                'ecommerce' => [
                    'fr_FR' => 'stark .one value per channel ecommerce / one value per locale fr_FR.',
                    'en_US' => 'stark',
                ],
                'mobile'    => [
                    'fr_FR' => 'stark',
                    'en_US' => 'stark',
                ],
            ]
        );
    }
}

