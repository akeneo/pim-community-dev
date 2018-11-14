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

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Record\Connector;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use PhpSpec\ObjectBehavior;

class ConnectorRecordSpec extends ObjectBehavior
{
     function let()
    {
        $recordCode = RecordCode::fromString('starck');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ]);
        $valueCollection = [
            'description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'fr_FR',
                    'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                ],
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                ],
            ],
            'short_description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                ],
            ]
        ];

        $this->beConstructedWith(
            $recordCode,
            $labelCollection,
            Image::createEmpty(),
            $valueCollection
        );
    }

     function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorRecord::class);
    }

     function it_normalizes_itself()
     {
         $this->normalize()->shouldReturn([
             'code' => 'starck',
             'labels'                   => [
                 'en_US' => 'Stark',
                 'fr_FR' => 'Stark',
             ],
             'values' => [
                 'description' => [
                     [
                         'channel'   => 'ecommerce',
                         'locale'    => 'fr_FR',
                         'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                     ],
                     [
                         'channel'   => 'ecommerce',
                         'locale'    => 'en_US',
                         'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                     ],
                 ],
                 'short_description' => [
                     [
                         'channel'   => 'ecommerce',
                         'locale'    => 'en_US',
                         'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                     ],
                 ]
             ],
             'main_image' => null,
         ]);
     }

     function it_filters_values_by_channel()
     {
         $recordCode = RecordCode::fromString('starck');
         $labelCollection = LabelCollection::fromArray([
             'en_US' => 'Stark',
             'fr_FR' => 'Stark'
         ]);
         $valueCollection = [
             'description' => [
                 [
                     'channel'   => 'ecommerce',
                     'locale'    => 'en_US',
                     'data'      => 'Description for e-commerce channel.',
                 ],
                 [
                     'channel'   => 'tablet',
                     'locale'    => 'en_US',
                     'data'      => 'Description for tablet channel.',
                 ],
             ],
             'short_description' => [
                 [
                     'channel'   => 'tablet',
                     'locale'    => 'en_US',
                     'data'      => 'Short description for tablet channel.',
                 ],
             ],
             'not_scopable_value' => [
                 [
                     'channel' => null,
                     'locale'  => 'en_US',
                     'data'    => 'Not scopable value.'
                 ]
             ]
         ];

         $this->beConstructedWith(
             $recordCode,
             $labelCollection,
             Image::createEmpty(),
             $valueCollection
         );

         $expectedRecord = new ConnectorRecord(
             $recordCode,
             $labelCollection,
             Image::createEmpty(),
             [
                 'description' => [
                     [
                         'channel' => 'ecommerce',
                         'locale'  => 'en_US',
                         'data'    => 'Description for e-commerce channel.',
                     ],
                 ],
                 'not_scopable_value' => [
                     [
                         'channel' => null,
                         'locale'  => 'en_US',
                         'data'    => 'Not scopable value.'
                     ]
                 ]
             ]
         );

         $this->filterValuesByChannel(ChannelIdentifier::fromCode('ecommerce'))->shouldBeLike($expectedRecord);
     }
}
