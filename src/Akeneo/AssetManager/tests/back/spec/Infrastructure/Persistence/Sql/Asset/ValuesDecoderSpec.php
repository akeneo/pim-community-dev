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

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use PhpSpec\ObjectBehavior;

class ValuesDecoderSpec extends ObjectBehavior
{
    function it_decodes_values()
    {
        $values = '
        {
          "label_foo_en_US": {
            "data": "foo label",
            "locale": "en_US",
            "channel": null,
            "attribute": "label_foo"
          },
          "att_bar_en_US": {
            "data": "bar value",
            "locale": "en_US",
            "channel": null,
            "attribute": "att_bar"
          }
        }';

        $expectedDecodedValues = [
            'label_foo_en_US' => [
                'data' => 'foo label',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'label_foo',
            ],
            'att_bar_en_US' => [
                'data' => 'bar value',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'att_bar',
            ],
        ];

        $this->decode($values)->shouldBeLike($expectedDecodedValues);
    }

    function it_decodes_values_and_clean_html()
    {
        $values = '
        {
          "label_foo_en_US": {
            "data": "foo label",
            "locale": "en_US",
            "channel": null,
            "attribute": "label_foo"
          },
          "att_bar_en_US": {
            "data": "<p>my value <span>with tags</span></p>",
            "locale": "en_US",
            "channel": null,
            "attribute": "att_bar"
          }
        }';

        $expectedDecodedValues = [
            'label_foo_en_US' => [
                'data' => 'foo label',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'label_foo',
            ],
            'att_bar_en_US' => [
                'data' => 'my value with tags',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'att_bar',
            ],
        ];

        $this->decode($values)->shouldBeLike($expectedDecodedValues);
    }

    function it_decodes_values_and_clean_html_but_not_invalid_tags()
    {
        $values = '
        {
          "label_foo_en_US": {
            "data": "foo label",
            "locale": "en_US",
            "channel": null,
            "attribute": "label_foo"
          },
          "att_bar_en_US": {
            "data": "<p>my value <span>with >89 tags</span></p> <20%",
            "locale": "en_US",
            "channel": null,
            "attribute": "att_bar"
          }
        }';

        $expectedDecodedValues = [
            'label_foo_en_US' => [
                'data' => 'foo label',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'label_foo',
            ],
            'att_bar_en_US' => [
                'data' => 'my value with >89 tags <20%',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'att_bar',
            ],
        ];

        $this->decode($values)->shouldBeLike($expectedDecodedValues);
    }

    function it_decodes_non_string_values()
    {
        $values = '
        {
          "label_foo_en_US": {
            "data": "foo label",
            "locale": "en_US",
            "channel": null,
            "attribute": "label_foo"
          },
          "att_bar_en_US": {
            "data": [{"bar1": "fubar"},{"bar2": "snafu"}],
            "locale": "en_US",
            "channel": null,
            "attribute": "att_bar"
          }
        }';

        $expectedDecodedValues = [
            'label_foo_en_US' => [
                'data' => 'foo label',
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'label_foo',
            ],
            'att_bar_en_US' => [
                'data' => [
                    0 => ['bar1' => 'fubar'],
                    1 => ['bar2' => 'snafu'],
                ],
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => 'att_bar',
            ],
        ];

        $this->decode($values)->shouldBeLike($expectedDecodedValues);
    }

    function it_throws_on_invalid_json()
    {
        $values = '
        {
          "label_foo_en_US": {
            "data": "foo label",
            "locale": "en_US",
            "channel": null,
            "attribute": "label_foo"
          },
          "att_bar_en_US": {
            "data": ",
            "locale": "en_US",
            "channel": null,
            "attribute": "att_bar"
          }
        }';

        $this->shouldThrow(\RuntimeException::class)
            ->during('decode', [$values]);
    }
}
